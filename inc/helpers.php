<?php
/**
 * Assorted Helpers
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Get an array of post types that use this plugin to manage bylines.
 *
 * @return array Post types.
 */
function get_supported_post_types() {
	$post_types = get_post_types_by_support( 'author' );

	/**
	 * Filter the list of supported post types. Defaults to all post types
	 * supporting 'author'.
	 *
	 * @param array $post_types Post types with which to use Byline Manager.
	 */
	return apply_filters( 'byline_manager_supported_post_types', $post_types );
}

/**
 * Given a post, get the profile objects that build up its byline.
 *
 * @param \WP_Post|int $post Optional. Post object or ID. Defaults to current
 *                           global post.
 * @return array Profile post objects.
 */
function get_profiles_for_post( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}

	$byline = get_post_meta( $post->ID, 'byline', true );
	if ( ! is_array( $byline ) ) {
		return [];
	}

	return array_filter( array_map( function( $meta ) {
		$profile = get_post( $meta['post_id'] );
		if ( $profile && PROFILE_POST_TYPE === $profile->post_type ) {
			return $profile;
		}
	}, $byline ) );
}
