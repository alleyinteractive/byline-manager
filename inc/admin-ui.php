<?php
/**
 * Admin Interfaces
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Register meta boxes used by the plugin.
 */
function register_meta_boxes() {
	add_meta_box(
		'byline-manager',
		__( 'Byline', 'byline-manager' ),
		__NAMESPACE__ . '\byline_meta_box',
		get_supported_post_types()
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
 * @param \WP_Post $profile Post object.
 * @return array {
 *     Necessary data to build the meta box.
 *
 *     @type int    $id        Post ID.
 *     @type int    $byline_id Term ID.
 *     @type string $name      Profile name (author name).
 *     @type string $image     URL for profile's image.
 * }
 */
function get_profile_data( $profile ) {
	return [
		'id'        => $profile->ID,
		'byline_id' => absint( get_post_meta( $profile->ID, 'byline_id', true ) ),
		'name'      => $profile->post_title,
		'image'     => get_the_post_thumbnail_url( $profile, [ 50, 50 ] ),
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
	if ( ! in_array( $post->post_type, get_supported_post_types(), true ) ) {
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
	wp_set_object_terms( $post_id, $byline_ids, BYLINE_TAXONOMY, false );

	// Set the byline meta on the post.
	$byline = array_map( function( $byline_id ) {
		$term = get_term( $byline_id, BYLINE_TAXONOMY );
		if ( ! $term instanceof \WP_Term ) {
			return null;
		}
		list( , $post_id ) = explode( '-', $term->name );
		return [
			'term_id' => $byline_id,
			'post_id' => absint( $post_id ),
		];
	}, $byline_ids );

	/**
	 * Filter the meta associated with a byline, allowing plugins to store
	 * additional information about the post's byline.
	 *
	 * @param array    $byline Byline metadata.
	 * @param \WP_Post $post   Post object being saved.
	 */
	$byline = apply_filters( 'byline_manager_post_byline_meta', $byline, $post );
	update_post_meta( $post_id, 'byline', $byline );
}
add_action( 'save_post', __NAMESPACE__ . '\set_byline', 10, 2 );
