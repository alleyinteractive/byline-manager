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
			'description' => __( 'A TextProfile type.', 'byline-manager' ),
			'fields'      => [
				'text' => [
					'type'        => 'String',
					'description' => __( 'Text for a text profile.', 'byline-manager' ),
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

	// Register options for byline format.
	register_graphql_enum_type(
		'BylineTextEnum',
		[
			'description' => __( 'Byline format.', 'byline-manager' ),
			'values'      => [
				'TEXT'       => [
					'value' => 'text',
				],
				'LINKS'      => [
					'value' => 'links',
				],
				'POST_LINKS' => [
					'value' => 'post_links',
				],
			],
		]
	);

	// Register the Byline object type.
	register_graphql_object_type(
		'Byline',
		[
			'description' => __( 'Byline manager byline.', 'byline-manager' ),
			'fields'      => [
				'bylineText' => [
					'args'        => [
						'format' => [
							'type'        => 'BylineTextEnum',
							'description' => __( 'Choose the byline format.', 'byline-manager' ),
						],
					],
					'type'        => 'String',
					'description' => __( 'Byline text.', 'byline-manager' ),
					'resolve'     => function( WPGraphQL\Model\Post $post, array $args ) {
						switch ( $args['format'] ) {
							case 'links':
								return get_the_byline_links( $post->ID );

							case 'post_links':
								return get_the_byline_posts_links( $post->ID );
						}

						// Default handling of 'text' formats.
						return get_the_byline( $post->ID );
					},
				],
				'profiles'   => [
					'type'        => [ 'list_of' => 'ProfileTypes' ],
					'description' => __( 'Byline profiles.', 'byline-manager' ),
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
				],
			],
		]
	);

	/*
	 * Register byline fields on nodes that support authors, which is also
	 * the check byline manager uses to determine if a post supports bylines.
	 */
	register_graphql_field(
		'NodeWithAuthor',
		'byline',
		[
			'type'        => 'Byline',
			'description' => __( 'Byline profiles.', 'byline-manager' ),
			'resolve'     => function ( WPGraphQL\Model\Post $post ) {
				return $post;
			},
		]
	);
}
