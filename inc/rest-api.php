<?php
/**
 * This file contains REST API endpoints
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;

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
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\rest_profile_search',
		]
	);
	register_rest_route(
		REST_NAMESPACE,
		'/users',
		[
			'methods'  => \WP_REST_Server::READABLE,
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
			[ __NAMESPACE__ . '\Models\Profile', 'get_by_post' ],
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
 */
function register_rest_fields() {
	register_rest_field(
		[ 'post' ],
		'byline',
		[
			'schema'          => [
				'description' => __( 'The byline of an article.', 'byline-manager' ),
				'type'        => 'object',
				'properties'  => [
					'rendered' => [
						'description' => __( 'The rendered byline content for the block.', 'byline-manager' ),
						'type'        => 'string',
						'context'     => [ 'view', 'edit' ],
						'readonly'    => false,
					],
				],
				'context'     => [ 'view', 'edit' ],
				'readonly'    => false,
			],
			'get_callback'    => __NAMESPACE__ . '\get_byline_field',
			'update_callback' => __NAMESPACE__ . '\update_byline_field',
		]
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_fields' );

/**
 * Callback to get Byline REST field values.
 *
 * @param  object           $object      The post object.
 * @param  string           $key         The key of register_rest_field.
 * @param  \WP_REST_Request $request     The full request.
 * @param  string           $object_type The object type from the schema.
 *
 * @return array    The byline values ['raw' => '...', 'rendered' => '...']
 */
function get_byline_field( $object, $key, $request, $object_type ) {
	$byline = get_post_meta( $object['id'], 'byline', true );

	if ( empty( $byline['items'] ) || ! is_array( $byline['items'] ) ) {
		return '';
	}

	// Render the byline parts back to block markup.
	$byline_rendered = '';
	foreach ( $byline['items'] as $item ) {
		if ( 'byline_id' === $item['type'] ) {
			$profile = Profile::get_by_post( $item['atts']['post_id'] );

			// link with data attributes and text.
			$byline_rendered .= '<a href="#' . $item['atts']['post_id'] . '" data-profile-id="' . $item['atts']['post_id'] . '" class="byline-manager-author">' . $profile->display_name . '</a>';
		} elseif ( 'text' === $item['type'] ) {
			// link with text.
			$byline_rendered .= '<a href="#" class="byline-manager-author">' . $item['atts']['text'] . '</a>';
		} elseif ( 'separator' === $item['type'] ) {
			// Just text.
			$byline_rendered .= $item['atts']['text'];
		}
	}

	$byline_content = [
		'rendered' => $byline_rendered,
	];

	return $byline_content;
}

/**
 * Callback to update Byline REST field values.
 *
 * @param  array            $value      The posted value.
 * @param  object           $object     The post object.
 * @param  string           $key         The key of register_rest_field.
 * @param  \WP_REST_Request $request     The full request.
 * @param  string           $object_type The object type from the schema.
 *
 * @return mixed    Result of the update post meta, will also accept \WP_Error.
 */
function update_byline_field( $value, $object, $key, $request, $object_type ) {
	// Don't set bylines on autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Only proceed for permitted post types.
	if ( ! Utils::is_post_type_supported( $object->post_type ) ) {
		return;
	}

	if ( ! empty( $value['rendered'] ) ) {
		$byline_parsed = Utils::byline_data_from_markup( trim( $value['rendered'] ) );
		Utils::set_post_byline( $object->ID, $byline_parsed );
		return update_post_meta( $object->ID, 'byline', $byline_parsed );
	} else {
		return delete_post_meta( $object->ID, 'byline' );
	}
}
