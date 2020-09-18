<?php
/**
 * This file contains REST API endpoints
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;

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
		'/hydrateProfiles',
		[
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => __NAMESPACE__ . '\rest_hydrate_profiles',
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
	$posts    = get_posts(
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
 * Hydrate profiles ids.
 *
 * @param \WP_REST_Request $request REST request data.
 * @return \WP_REST_Response REST API response.
 */
function rest_hydrate_profiles( \WP_REST_Request $request ) {
	$byline_profiles = $request['profiles'] ?? [];
	$profiles        = [];

	if ( ! empty( $byline_profiles ) ) {
		$index = 0;

		foreach ( $byline_profiles as $entry ) {
			if (
				! empty( $entry['type'] )
				&& 'byline_id' === $entry['type']
				&& ! empty( $entry['atts']['post_id'] )
			) {
				// Handle byline profile ID entries.
				$profile = Profile::get_by_post( $entry['atts']['post_id'] );
				if ( $profile instanceof Profile ) {
					$profiles[] = get_profile_data_for_meta_box( $profile );
				}
			} elseif ( ! empty( $entry['atts']['text'] ) ) {
				// Handle text-only bylines.
				$text_profile = TextProfile::create( $entry['atts'] );
				$profiles[]   = [
					// Uses a semi-arbitrary ID to give the script a reference point.
					'id'   => $text_profile->id,
					'name' => $text_profile->display_name,
				];
			}
			$index++;
		}

		$byline_profiles = $profiles;
	}

	// Send the response.
	return rest_ensure_response( $profiles );
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

register_post_meta(
	'',
	'byline',
	[
		'single'       => true,
		'type'         => 'object',
		'show_in_rest' => [
			'schema' => [
				'type'       => 'object',
				'properties' => [
					'profiles' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'type' => [
									'type' => 'string',
								],
								'atts' => [
									'type'       => 'object',
									'properties' => [
										'term_id' => [
											'type' => 'integer',
										],
										'post_id' => [
											'type' => 'integer',
										],
										'text'    => [
											'type' => 'string',
										],
									],
								],
							],
						],
					],
				],
			],
		],
	]
);
