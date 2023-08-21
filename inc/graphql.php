<?php
/**
 * This file contains functionality needed to integrate with WPGraphQL.
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use WPGraphQL\Model\Post;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

add_action( 'graphql_register_types', __NAMESPACE__ . '\register_byline_types' );
add_action( 'graphql_register_types', __NAMESPACE__ . '\register_byline_post_connection' );

/**
 * Register data types and field for supporting bylines on posts.
 */
function register_byline_types(): void {
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
					$profile instanceof Post
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
					'resolve'     => function( Post $profile, array $args ) {
						$post_id = $profile->ID;

						if ( $profile->isPreview ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$post_id = $profile->parentDatabaseId; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						}

						$format = $args['format'] ?? 'text';

						switch ( $format ) {
							case 'links':
								return get_the_byline_links( $post_id );

							case 'post_links':
								return get_the_byline_posts_links( $post_id );

							case 'text':
							default:
								return get_the_byline( $post_id );
						}
					},
				],
				'profiles'   => [
					'type'        => [ 'list_of' => 'ProfileTypes' ],
					'description' => __( 'Byline profiles.', 'byline-manager' ),
					'resolve'     => function ( Post $profile ) {
						$post_id = $profile->ID;

						if ( $profile->isPreview ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$post_id = $profile->parentDatabaseId; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						}

						$byline_data = get_post_meta( $post_id, 'byline', true );

						if ( empty( $byline_data ) ) {
							return null;
						}

						$bylines = array_map(
							function( $profile ) {
								if ( 'byline_id' === $profile['type'] ) {
									return new Post( get_post( $profile['atts']['post_id'] ) );
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
			'resolve'     => function ( Post $post ) {
				return $post;
			},
		]
	);
}

/**
 * Register a connection between profiles and their associated posts.
 */
function register_byline_post_connection(): void {
	register_graphql_connection(
		[
			'fromType'           => 'Profile',
			'toType'             => 'Post',
			'fromFieldName'      => 'profilePosts',
			'connectionTypeName' => 'PostsFromProfileConnection',
			'resolve'            => function ( Post $profile, $args, $context, $info ) {
				$profile   = Models\Profile::get_by_post( $profile->ID );
				$byline_id = $profile->term_id;
				$resolver  = new PostObjectConnectionResolver( $profile, $args, $context, $info );

				$resolver->set_query_arg(
					'tax_query',
					[
						[
							'taxonomy' => BYLINE_TAXONOMY,
							'terms'    => $byline_id,
						],
					],
				);

				return $resolver->get_connection();
			},
		]
	);
}
