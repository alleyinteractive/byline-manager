<?php
/**
 * This file contains REST API endpoints
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * REST API namespace.
 *
 * @var string
 */
const REST_NAMESPACE = 'byline-manager/v1';

/**
 * Register the REST API routes.
 */
function register_rest_routes() {
	register_rest_route(
		REST_NAMESPACE,
		'/authors',
		[
			'methods' => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\rest_profile_search',
		]
	);
	register_rest_route(
		REST_NAMESPACE,
		'/users',
		[
			'methods' => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\rest_user_search',
		]
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Send API response for REST endpoint.
 *
 * @param \WP_REST_Request $request REST request data.
 * @return \WP_REST_Response REST API response.
 */
function rest_profile_search( \WP_REST_Request $request ) {
	$posts = get_posts(
		[
			'post_type'        => PROFILE_POST_TYPE,
			's'                => $request->get_param( 's' ),
			'suppress_filters' => false,
			'orderby'          => 'title',
			'order'            => 'asc',
		]
	);
	$profiles = array_filter(
		array_map(
			[ 'Byline_Manager\Models\Profile', 'get_by_post' ],
			$posts
		)
	);

	// Build the REST response data.
	$data = array_map( __NAMESPACE__ . '\get_profile_data_for_meta_box', $profiles );

	// Send the response.
	return rest_ensure_response( $data );
}

/**
 * Send API response for REST endpoint.
 *
 * @param \WP_REST_Request $request REST request data.
 * @return \WP_REST_Response REST API response.
 */
function rest_user_search( \WP_REST_Request $request ) {
	$users = get_users(
		[
			'search'  => $request->get_param( 's' ) . '*',
			'orderby' => 'display_name',
		]
	);

	// Build the REST response data.
	$data = array_map(
		__NAMESPACE__ . '\get_user_data_for_meta_box',
		$users,
		array_fill( 0, count( $users ), absint( $request->get_param( 'post' ) ) )
	);

	// Send the response.
	return rest_ensure_response( $data );
}

/**
 * Defines custom REST API fields.
 * This will be expose with the posts REST, i.e. /wp-json/wp/v2/posts/[post_id]
 * @todo switch this from the demo post meta field to the real one.
 *
 */
function register_rest_fields() {
	register_rest_field(
		[ 'post' ],
		'byline',
		[
			'schema'       => [
				'description' => __( 'The byline of an article.', 'byline-manager' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => false,
			],
			'get_callback' => function ( $object, $field_name, $request, $object_type ) {
				// @todo - process byline value to string.
				return get_post_meta( $object['id'], 'byline_demo', true );
			},
			'update_callback' => function ( $value, $object, $field_name) {
				// @todo - process byline value from string.
				return update_post_meta( $object['id'], 'byline_demo', $value );
			}
		]
	);
}

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_fields' );

/**
 * Set up some demo data for testing purposes.
 * This is testing data only!
 * @todo - Remove this, once we have real byline data wired up.
 *
 * @package Summary
 */
function set_post_demo_byline_meta( $post_object) {
	// We will need to display the data in a Gutenberg markup, but store it in an easier to manipulate structure, like:
	/*
	[
		'source' => 'manual',
		'items' => [
			[
				'type' => 'byline_id',
				'atts' => [
					'term_id' => 123,
					'post_id' => 456,
				],
			],
			[
				'type' => 'separator',
				'atts' => [
					'text' => ' and ',
				],
			],
			[
				'type' => 'text',
				'atts' => [
					'text' => 'John Smith',
				],
			],
			[
				'type' => 'separator',
				'atts' => [
					'text' => ' in England',
				],
			],
		],
	]

	Which would convert to this markup:
	*/

	$byline_demo = 'By <span class="byline-author" data-profile-id="456">Jane Doe</span> and <span  class="byline-author" data-profile-id="">John Smith</span> in England.';

	// Let's set this as the default value for testing.
	update_post_meta( $post_object->ID, 'byline_demo', $byline_demo );
}

add_action( 'the_post',  __NAMESPACE__ . '\set_post_demo_byline_meta' );
