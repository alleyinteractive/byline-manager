<?php
/**
 * Assorted utility methods
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;

/**
 * Utility methods for managing profiles and posts' bylines.
 */
class Utils {

	/**
	 * Get an array of post types that use this plugin to manage bylines.
	 *
	 * @return array Post types.
	 */
	public static function get_supported_post_types() {
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
	 * @return array Profile objects.
	 */
	public static function get_profiles_for_post( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return [];
		}

		$byline = get_post_meta( $post->ID, 'byline', true );
		if ( ! is_array( $byline ) ) {
			return [];
		}

		return array_filter( array_map( function( $meta ) {
			return ! empty( $meta['post_id'] )
				? Profile::get_by_post( $meta['post_id'] )
				: false;
		}, $byline ) );
	}

	/**
	 * Given a byline ID (term ID), get the post ID associated with the profile.
	 *
	 * @param int $byline_id Term ID.
	 * @return int|false Post ID on success, false if the term is invalid, 0 if
	 *                   the post ID was not found.
	 */
	public static function get_profile_id_by_byline_id( $byline_id ) {
		$term = get_term( $byline_id, BYLINE_TAXONOMY );
		if ( ! $term instanceof \WP_Term ) {
			return false;
		}
		list( , $post_id ) = explode( '-', $term->name );
		return absint( $post_id );
	}

	/**
	 * Set the byline for a post given a set of byline ids (term ids).
	 *
	 * @param int   $post_id    ID for the post to modify.
	 * @param array $byline_ids Term IDs for bylines to connect to the post.
	 */
	public static function set_post_byline( int $post_id, array $byline_ids ) {
		wp_set_object_terms( $post_id, $byline_ids, BYLINE_TAXONOMY, false );

		// Set the byline meta on the post.
		$byline = array_map( function( $term_id ) {
			$post_id = Utils::get_profile_id_by_byline_id( $term_id );
			return $post_id ? compact( 'term_id', 'post_id' ) : null;
		}, $byline_ids );

		/**
		 * Filter the meta associated with a byline, allowing plugins to store
		 * additional information about the post's byline.
		 *
		 * @param array    $byline  Byline metadata.
		 * @param \WP_Post $post_id ID of post getting the byline.
		 */
		$byline = apply_filters( 'byline_manager_post_byline_meta', $byline, $post_id );
		update_post_meta( $post_id, 'byline', $byline );
	}
}
