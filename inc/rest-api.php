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
 *
 * The byline has two representations:
 *  - "rendered" is the rich text html markup for the content of the block.
 *  - "raw" is the structured object stored in the post meta.
 *
 * ['rendered'] =>
 *  'By <span data-profile-id="456" class="byline-author">Jane Doe</span> and
 *  <span data-profile-id="" class="byline-author">John Smith</span> in England'
 *
 * ['raw'] =>
 *  [
 *    'source' => 'manual',
 *    'items' => [
 *      [
 *        'type' => 'byline_id',
 *        'atts' => [
 *          'term_id' => 123,
 *          'post_id' => 456,
 *        ],
 *      ],
 *      [
 *        'type' => 'separator',
 *        'atts' => [
 *          'text' => ' and ',
 *        ],
 *      ],
 *      [
 *        'type' => 'text',
 *        'atts' => [
 *          'text' => 'John Smith',
 *        ],
 *      ],
 *      [
 *        'type' => 'separator',
 *        'atts' => [
 *          'text' => ' in England',
 *        ],
 *      ],
 *    ],
 *  ]
 *
 * @todo Add the byline raw value.
 */
function register_rest_fields() {
	register_rest_field(
		[ 'post' ],
		'byline',
		[
			'schema'          => [
				'description' => __( 'The byline of an article.', 'byline-manager' ),
				'type'        => 'object',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => false,
			],
			'get_callback'    => function ( $object, $field_name, $request, $object_type ) {
				return get_post_meta( $object['id'], 'byline', true );
			},
			'update_callback' => function ( $value, $object, $field_name ) {
				return update_post_meta( $object->ID, 'byline', $value );
			},
		]
	);
}

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
	$content_rendered = get_post_meta( $object['id'], 'byline_rendered', true );

	$$byline_content = [
		[ 'rendered' ] => $content_rendered,
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
	$content_rendered = '';

	if ( ! empty( $value['rendered'] ) ) {
		$content_rendered = $value['rendered'];
	}
	$content['rendered'] = $content_rendered;

	return update_post_meta( $object->ID, 'byline_rendered', $value );
}

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_fields' );
