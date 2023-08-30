<?php
/**
 * This file contains REST API endpoints
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * REST API namespace.
 *
 * @var string
 */
const REST_NAMESPACE = 'byline-manager/v1';

/**
 * Register the REST API routes.
 */
function register_rest_routes(): void {
	register_rest_route(
		REST_NAMESPACE,
		'/authors',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\rest_profile_search',
			'permission_callback' => 'is_user_logged_in',
		]
	);
	register_rest_route(
		REST_NAMESPACE,
		'/users',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\rest_user_search',
			'permission_callback' => 'is_user_logged_in',
		]
	);
	register_rest_route(
		REST_NAMESPACE,
		'/hydrateProfiles',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => __NAMESPACE__ . '\rest_hydrate_profiles',
			'permission_callback' => 'is_user_logged_in',
		]
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Send API response for REST endpoint.
 *
 * @param WP_REST_Request $request REST request data.
 * @return WP_REST_Response REST API response.
 */
function rest_profile_search( WP_REST_Request $request ): WP_REST_Response {
	$posts = get_posts( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		[
			'post_type'        => PROFILE_POST_TYPE,
			'numberposts'      => 10,
			's'                => $request->get_param( 's' ),
			'suppress_filters' => false,
			'orderby'          => 'relevance',
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
 * @param WP_REST_Request $request REST request data.
 * @return WP_REST_Response REST API response.
 */
function rest_hydrate_profiles( WP_REST_Request $request ): WP_REST_Response {
	$byline_profiles = $request['profiles'] ?? [];
	$profiles        = [];

	// Check to see if the current user has profile associated with their user.
	$current_user_profile_id = get_user_meta( get_current_user_id(), 'profile_id', true );
	$current_user_profile    = Profile::get_by_post( $current_user_profile_id );

	/**
	 * Determine whether to auto set byline if a user object has a byline associated with it.
	 *
	 * @param bool $value Whether or not to auto set profile.
	 */
	$auto_set_user_profile = apply_filters( 'byline_manager_auto_set_user_profile', true );

	/**
	 * If we have bylines, hydrate them with meta values.
	 * Else return the user's associated profile if one was found and $auto_set_user_profile === true.
	 */
	if ( ! empty( $byline_profiles ) ) {
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
		}
	} elseif ( $current_user_profile instanceof Profile && $auto_set_user_profile ) {
		$profiles[] = get_profile_data_for_meta_box( $current_user_profile );
	}

	// Send the response.
	return rest_ensure_response( $profiles );
}

/**
 * Send API response for REST endpoint.
 *
 * @param WP_REST_Request $request REST request data.
 * @return WP_REST_Response REST API response.
 */
function rest_user_search( WP_REST_Request $request ): WP_REST_Response {
	$post_id = absint( $request->get_param( 'post' ) );
	$users   = get_users(
		[
			'search'  => $request->get_param( 's' ) . '*',
			'orderby' => 'display_name',
		]
	);

	// Build the REST response data.
	$data = array_map(
		fn ( $user ) => get_user_data_for_meta_box( $user, absint( $post_id ) ),
		$users,
		array_fill( 0, count( $users ), $post_id )
	);

	// Send the response.
	return rest_ensure_response( $data );
}

/**
 * Meta schema for byline meta.
 *
 * This function is useful for third party developers
 * creating custom slotfills using our redux store.
 *
 * @return array
 */
function byline_meta_schema(): array {
	return [
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
	];
}

register_post_meta(
	'',
	'byline',
	[
		'single'       => true,
		'type'         => 'object',
		'show_in_rest' => [
			'schema' => byline_meta_schema(),
		],
	]
);
