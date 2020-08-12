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
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\rest_profile_search',
			'permission_callback' => '__return_true',
		]
	);
	register_rest_route(
		REST_NAMESPACE,
		'/users',
		[
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\rest_user_search',
			'permission_callback' => '__return_true',
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
