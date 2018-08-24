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
		'byline-manager',
		__( 'Byline', 'byline-manager' ),
		__NAMESPACE__ . '\byline_meta_box',
		Utils::get_supported_post_types()
	);
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_meta_boxes' );

/**
 * Output the byline meta box.
 *
 * @param \WP_Post $post Post object.
 */
function byline_meta_box( $post ) {
	echo '<div id="byline-manager-metabox-root"></div>';
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
