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
	register_rest_route( REST_NAMESPACE, '/authors', [
		'methods' => \WP_REST_Server::READABLE,
		'callback' => __NAMESPACE__ . '\rest_search',
	] );
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Send API response for REST endpoint.
 *
 * @param \WP_REST_Request $request REST request data.
 * @return \WP_REST_Response REST API response.
 */
function rest_search( \WP_REST_Request $request ) {
	$authors = get_posts( [
		'post_type'        => PROFILE_POST_TYPE,
		's'                => $request->get_param( 's' ),
		'suppress_filters' => false,
		'orderby'          => 'title',
		'order'            => 'asc',
	] );

	// Build the REST response data.
	$data = array_map( function( $author ) {
		return [
			'id'    => $author->ID,
			'name'  => $author->post_title,
			'image' => get_the_post_thumbnail_url( $author, [ 50, 50 ] ),
		];
	}, $authors );

	// Send the response.
	return rest_ensure_response( $data );
}
