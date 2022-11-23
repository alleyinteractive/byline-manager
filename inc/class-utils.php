<?php
/**
 * Assorted utility methods
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;

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
	 * Is the given (or current) post type supported by Byline Manager?
	 *
	 * @param string|null $post_type Optional. Post type slug. Defaults to
	 *                               current global post type.
	 * @return bool True if yes, false if no.
	 */
	public static function is_post_type_supported( $post_type = null ) {
		if ( ! $post_type ) {
			$post_type = get_post_type();
		}
		return in_array( $post_type, self::get_supported_post_types(), true );
	}

	/**
	 * Get the byline post meta for a post.
	 *
	 * @param \WP_Post|int $post Optional. Post object or ID. Defaults to
	 *                           current global post.
	 * @return array {
	 *     Metadata about the byline for the post.
	 *
	 *     @type string $source   One of 'profiles' or 'override'.
	 *     @type string $override Byline override text.
	 *     @type array  $profiles Array of profile post IDs and byline term IDs
	 *                            for each profile.
	 * }
	 */
	public static function get_byline_meta_for_post( $post = null ) {
		$defaults = [
			'profiles' => [],
		];

		$post = get_post( $post );
		if ( $post ) {
			return wp_parse_args(
				get_post_meta( $post->ID, 'byline', true ),
				$defaults
			);
		}

		return $defaults;
	}

	/**
	 * Given a post, get the profile and text profile objects that build
	 * up its byline, if applicable.
	 *
	 * @param \WP_Post|int $post Optional. Post object or ID. Defaults to
	 *                           current global post.
	 * @return array Profile and TextProfile objects.
	 */
	public static function get_byline_entries_for_post( $post = null ) {
		$byline = self::get_byline_meta_for_post( $post );
		if (
			empty( $byline['profiles'] )
			|| ! is_array( $byline['profiles'] )
		) {
			return [];
		}

		return array_filter(
			array_map(
				function( $entry ) {
					if ( ! empty( $entry['atts']['post_id'] ) ) {
						return Profile::get_by_post( $entry['atts']['post_id'] );
					} elseif ( ! empty( $entry['atts']['text'] ) ) {
						return TextProfile::create( $entry['atts'] );
					}
					return false;
				},
				$byline['profiles']
			)
		);
	}

	/**
	 * Given a byline ID (term ID), get the post ID associated with the profile.
	 *
	 * @param int $byline_id Term ID.
	 * @return int|false Post ID on success, false if the term is invalid, 0 if
	 *                   the post ID was not found.
	 */
	public static function get_profile_id_by_byline_id( int $byline_id ) {
		$term = get_term( $byline_id, BYLINE_TAXONOMY );
		if ( ! $term instanceof \WP_Term ) {
			return false;
		}
		list( , $post_id ) = explode( '-', $term->name );
		return absint( $post_id );
	}

	/**
	 * Set the byline for a post given raw meta information.
	 *
	 * @param int   $post_id     ID for the post to modify.
	 * @param array $byline_meta Metadata about the byline to store.
	 *
	 * Example array for $byline_meta:
	 *
	 * [
	 *     'byline_entries' => [
	 *         [
	 *             'type' => 'byline_id',
	 *             'atts' => [
	 *                 'byline_id' => 123, // Term ID, int.
	 *             ],
	 *         ],
	 *         [
	 *             'type' => 'byline_id',
	 *             'atts' => [
	 *                 'byline_id' => 456, // Term ID, int.
	 *             ],
	 *         ],
	 *         [
	 *             'type' => 'text',
	 *             'atts' => [
	 *                 'text' => 'A Text-only Author', // A text profile, string.
	 *             ],
	 *         ],
	 *     ],
	 * ].
	 */
	public static function set_post_byline( int $post_id, array $byline_meta ) {
		$default_args = [
			'byline_entries' => [],
		];
		$byline_meta  = wp_parse_args( $byline_meta, $default_args );

		// Extract the term IDs from the byline meta.
		$byline_terms = array_map(
			function( $entry ) {
				if ( empty( $entry['type'] ) || 'byline_id' !== $entry['type'] || empty( $entry['atts']['byline_id'] ) ) {
					return null;
				} else {
					return $entry['atts']['byline_id'];
				}
			},
			$byline_meta['byline_entries']
		);

		// Set the terms.
		wp_set_object_terms( $post_id, array_filter( $byline_terms ), BYLINE_TAXONOMY, false );

		// Set the byline meta on the post, handling both byline IDs and text items.
		$profiles = array_map(
			function( $entry ) {
				if ( empty( $entry['type'] ) || empty( $entry['atts'] ) ) {
					// We don't have enough info to process this entry.
					return null;
				} elseif ( 'text' === $entry['type'] ) {
					// Return text entries as is.
					return $entry;
				} elseif ( 'byline_id' === $entry['type'] && ! empty( $entry['atts']['byline_id'] ) ) {
					$post_id = Utils::get_profile_id_by_byline_id( $entry['atts']['byline_id'] );
					if ( ! empty( $post_id ) ) {
						return [
							'type' => 'byline_id',
							'atts' => [
								'term_id' => $entry['atts']['byline_id'],
								'post_id' => $post_id,
							],
						];
					}
				}
				// None of the above matched!
				return null;
			},
			$byline_meta['byline_entries']
		);

		$byline = [
			'profiles' => array_filter( $profiles ),
		];

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

	/**
	 * Get or create a byline based on a slug.
	 *
	 * @param string $byline_slug Byline slug.
	 * @param string $byline_title Byline title.
	 * @param string $content Byline post content. Optional.
	 * @return Profile Byline profile object.
	 */
	public static function get_or_create_byline( $byline_slug, $byline_title, $content = '' ) {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		$byline_query = get_posts(
			[
				'post_type'        => PROFILE_POST_TYPE,
				'name'             => $byline_slug,
				'post_status'      => 'publish',
				'numberposts'      => 1,
				'suppress_filters' => false,
			]
		);

		if ( ! empty( $byline_query[0]->ID ) ) {
			return Profile::get_by_post( $byline_query[0] );
		}

		return Profile::create(
			[
				'post_title'   => $byline_title ?? ' ',
				'post_name'    => $byline_slug,
				'post_content' => $content,
			]
		);
	}

	/**
	 * Assign bylines to a post.
	 *
	 * @param int   $post_id    Post ID.
	 * @param array $byline_ids Array of byline IDs.
	 */
	public static function assign_bylines_to_post( $post_id, $byline_ids ) {
		$meta = [
			'byline_entries' => [],
		];

		foreach ( $byline_ids as $byline_id ) {
			$meta['byline_entries'][] = [
				'type' => 'byline_id',
				'atts' => compact( 'byline_id' ),
			];
		}

		if ( ! empty( $meta['byline_entries'] ) ) {
			self::set_post_byline( $post_id, $meta );
		}
	}
}
