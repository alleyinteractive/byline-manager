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
			'items' => [],
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
			empty( $byline['items'] )
			|| ! is_array( $byline['items'] )
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
				$byline['items']
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
	 * @param array $byline_meta {
	 *     Metadata about the byline to store.
	 *
	 *     @type string $source     Optional. One of 'profiles' or 'override'.
	 *                              defaults to 'profiles'.
	 *     @type string $override   Optional. Byline override text. Defaults to
	 *                              empty string.
	 *     @type array  $byline_ids Optional. Byline term ids. Defaults to empty
	 *                              array.
	 * }
	 *
	 * @return bool Results of update to post meta.
	 */
	public static function set_post_byline( int $post_id, array $byline_meta ) {
		foreach ( $byline_meta['items'] as $index => $item ) {
			// Ensure byline_id items have both a byline ID and a post_id.
			if ( ! empty( $item['atts']['post_id'] ) && empty( $item['atts']['byline_id'] ) ) {
				$profile = Profile::get_by_post( $item['atts']['post_id'] );
				if ( ! is_null( $profile->byline_id ) ) {
					$byline_meta['items'][ $index ]['atts']['byline_id'] = $profile->byline_id;
				}
			} elseif ( ! empty( $item['atts']['byline_id'] ) && empty( $item['atts']['post_id'] ) ) {
				$profile = Profile::get_by_term_id( $item['atts']['byline_id'] );
				if ( ! is_null( $profile->post_id ) ) {
					$byline_meta['items'][ $index ]['atts']['post_id'] = $profile->post_id;
				}
			}
		}

		// Extract the term IDs from the byline meta.
		$byline_terms = array_map(
			function( $entry ) {
				if ( empty( $entry['type'] ) || 'byline_id' !== $entry['type'] || empty( $entry['atts']['byline_id'] ) ) {
					return null;
				} else {
					return $entry['atts']['byline_id'];
				}
			},
			$byline_meta['items']
		);

		// Set the terms.
		wp_set_object_terms( $post_id, array_filter( $byline_terms ), BYLINE_TAXONOMY, false );

		/**
		 * Filter the meta associated with a byline, allowing plugins to store
		 * additional information about the post's byline.
		 *
		 * @param array    $byline  Byline metadata.
		 * @param \WP_Post $post_id ID of post getting the byline.
		 */
		$byline = apply_filters( 'byline_manager_post_byline_meta', $byline_meta, $post_id );
		return update_post_meta( $post_id, 'byline', $byline );
	}

	public static function byline_data_from_markup( $markup ) {
		$byline_data = [
			'source' => 'manual',
			'items' => [],
		];
		$pattern = '#(<span.+</span>)#U';

		// Split the string on spans, including the spans and their content as items.
		$fragments = preg_split( $pattern, $markup, null, PREG_SPLIT_DELIM_CAPTURE );
		$fragments = array_filter( $fragments );

		foreach ( $fragments as $fragment ) {
			// Examine each fragment to construct a byline item.
			if ( false === strpos( $fragment, '<span' ) ) {
				// Add a separator item.
				$byline_data['items'][] = [
					'type' => 'separator',
					'atts' => [
						'text' => $fragment,
					],
				];
			} else {
				// Create a DOM for this item to get attributes.
				$dom = new \DOMDocument();
				$dom->loadHTML( '<html>' .  $fragment . '</html>' );

				// Find the spans and work with the first one.
				$spans = $dom->getElementsByTagName( 'span' );

				// Something went wrong--this isn't an author.
				if ( empty( $spans[0] ) || 'byline-manager-author' !== $spans[0]->getAttribute( 'class' ) ) {
					continue;
				}

				// See if it has a profile ID (or author ID, in the future.)
				if ( ! empty( $spans[0]->getAttribute( 'data-profile-id' ) ) ) {
					$profile_id = $spans[0]->getAttribute( 'data-profile-id' );
					$profile = Profile::get_by_post( $profile_id );

					// Look up term ID.
					$byline_data['items'][] = [
						'type' => 'byline_id',
						'atts' => [
							'byline_id' => $profile->term_id,
							'post_id' => $profile_id,
						],
					];
				} else {
					$byline_data['items'][] = [
						'type' => 'text',
						'atts' => [
							'text' => $spans[0]->textContent,
						],
					];
				}
			}
		}

		return $byline_data;
	}
}
