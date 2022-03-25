<?php
/**
 * This file contains functionality needed to integrate with WPGraphQL.
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use WPGraphQL;

add_action( 'graphql_register_types', __NAMESPACE__ . '\register_byline_types' );

/**
 * Register data types and field for supporting bylines on posts.
 */
function register_byline_types() {
	// Register a text profile.
	register_graphql_object_type(
		'TextProfile',
		[
			'description' => __( 'Promoted Book', 'byline-manager' ),
			'fields'      => [
				'text' => [
					'type'        => 'String',
					'description' => __( 'Text for a text profile', 'byline-manager' ),
				],
			],
		]
	);

	// Register a union for both Profile and TextProfile types.
	register_graphql_union_type(
		'ProfileTypes',
		[
			'typeNames'   => [ 'Profile', 'TextProfile' ],
			'resolveType' => function ( $profile ) {
				if (
					$profile instanceof WPGraphQL\Model\Post
					&& PROFILE_POST_TYPE === $profile->post_type
				) {
					return 'Profile';
				}

				return 'TextProfile';
			},
		]
	);

	/*
	 * Register byline fields on nodes that support authors, which is also
	 * the check byline manager uses to determine if a post supports bylines.
	 * The byline field will return a list of either Profile posts or text
	 * profiles based on the type stored in the post meta object
	 */
	register_graphql_field(
		'NodeWithAuthor',
		'byline',
		[
			'type'        => [ 'list_of' => 'ProfileTypes' ],
			'description' => __( 'Byline profiles', 'byline-manager' ),
			'resolve'     => function ( WPGraphQL\Model\Post $post ) {
				$byline_data = get_post_meta( $post->ID, 'byline', true );

				$bylines = array_map(
					function( $profile ) {
						if ( 'byline_id' === $profile['type'] ) {
							return new WPGraphQL\Model\Post( get_post( $profile['atts']['post_id'] ) );
						}

						return $profile['atts'];
					},
					$byline_data['profiles'],
				);

				return $bylines;
			},
		]
	);
}
