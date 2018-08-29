<?php
/**
 * Admin Interfaces
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;

/**
 * Register meta boxes used by the plugin.
 */
function register_meta_boxes() {
	add_meta_box(
		'byline-manager-byline-meta-box',
		__( 'Byline', 'byline-manager' ),
		__NAMESPACE__ . '\byline_meta_box',
		Utils::get_supported_post_types()
	);
	add_meta_box(
		'byline-manager-user-link-meta-box',
		__( 'User Account', 'byline-manager' ),
		__NAMESPACE__ . '\user_link_meta_box',
		PROFILE_POST_TYPE
	);
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_meta_boxes' );

/**
 * Output the byline meta box.
 *
 * @param \WP_Post $post Post object.
 */
function byline_meta_box( $post ) {
	wp_nonce_field( 'set_byline_data', 'post_byline_nonce' );
	echo '<div id="byline-manager-metabox-root"></div>';
}

/**
 * Output the user link meta box.
 *
 * @param \WP_Post $post Post object.
 */
function user_link_meta_box( $post ) {
	$stored_id = absint( get_post_meta( $post->ID, 'user_id', true ) );
	if ( ! empty( $stored_id ) ) {
		$user = get_user_by( 'id', $stored_id );
	} else {
		$user = wp_get_current_user();
	}
	if ( $user instanceof \WP_User ) {
		$userdata = get_user_data_for_meta_box( $user );
	} else {
		// Use stdClass so the JSON gets written as an empty object.
		$userdata = new stdClass();
	}

	wp_nonce_field( 'set_user_link', 'profile_user_link_nonce' );
	printf(
		'<div id="byline-manager-user-link-root" data-user="%s"></div>',
		esc_attr( wp_json_encode( $userdata ) )
	);
}

/**
 * Given a profile post object, build the necessary data needed by the meta box.
 *
 * @param Profile $profile Profile object.
 * @return array {
 *     Necessary data to build the meta box.
 *
 *     @type int    $id        Post ID.
 *     @type int    $byline_id Term ID.
 *     @type string $name      Profile name (author name).
 *     @type string $image     URL for profile's image.
 * }
 */
function get_profile_data_for_meta_box( Profile $profile ) {
	return [
		'id'        => $profile->post_id,
		'byline_id' => absint( get_post_meta( $profile->post_id, 'byline_id', true ) ),
		'name'      => $profile->display_name,
		'image'     => get_the_post_thumbnail_url( $profile->post_id, [ 50, 50 ] ),
	];
}

/**
 * Given a user object, build the data needed by the user link meta box.
 *
 * @param \WP_User $user User object.
 * @return array {
 *     Necessary data to build the meta box.
 *
 *     @type int    $id    User ID.
 *     @type string $name  Display Name.
 * }
 */
function get_user_data_for_meta_box( \WP_User $user ) {
	return [
		'id'   => $user->ID,
		'name' => $user->display_name,
	];
}

/**
 * Set the byline when a post is saved.
 *
 * @param int      $post_id Post ID being saved.
 * @param \WP_Post $post    Post object being saved.
 */
function set_byline( $post_id, $post ) {
	// Don't set bylines on autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Only proceed for permitted post types.
	if ( ! Utils::is_post_type_supported( $post->post_type ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if (
		empty( $_POST['post_byline_nonce'] )
		|| ! wp_verify_nonce( $_POST['post_byline_nonce'], 'set_byline_data' ) // WPCS: sanitization ok.
	) {
		return;
	}

	// Attach the byline terms to the post.
	$byline_ids = ! empty( $_POST['byline_profiles'] )
		? array_map( 'absint', $_POST['byline_profiles'] )
		: [];

	// Set the byline.
	Utils::set_post_byline( $post_id, $byline_ids );
}
add_action( 'save_post', __NAMESPACE__ . '\set_byline', 10, 2 );

/**
 * Set the user link when a profile post is saved.
 *
 * @param int      $post_id Post ID being saved.
 * @param \WP_Post $post    Post object being saved.
 */
function set_profile_user_link( $post_id, $post ) {
	// Don't set user link on autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Only proceed for the profile post type.
	if ( ! PROFILE_POST_TYPE === $post->post_type ) {
		return;
	}

	// Verify that the nonce is valid.
	if (
		empty( $_POST['profile_user_link_nonce'] )
		|| ! wp_verify_nonce( $_POST['profile_user_link_nonce'], 'set_user_link' ) // WPCS: sanitization ok.
	) {
		return;
	}

	$new_user_id = ! empty( $_POST['profile_user_link'] )
		? absint( $_POST['profile_user_link'] )
		: 0;

	// First, check to see if this has changed, for reciprocal updates.
	$old_user_id = absint( get_post_meta( $post_id, 'user_id', true ) );
	if ( $old_user_id && $old_user_id !== $new_user_id ) {
		delete_user_meta( $old_user_id, 'profile_id' );
		delete_post_meta( $post_id, 'user_id' );
	}

	// Save the post meta.
	if ( ! empty( $new_user_id ) ) {
		update_post_meta( $post_id, 'user_id', $new_user_id );
		update_user_meta( $new_user_id, 'profile_id', $post_id );
	}
}
add_action( 'save_post', __NAMESPACE__ . '\set_profile_user_link', 10, 2 );
