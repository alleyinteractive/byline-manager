<?php
/**
 * Admin Interfaces
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Register meta boxes used by the plugin.
 */
function register_meta_boxes() {
	add_meta_box(
		'byline-manager',
		__( 'Byline', 'byline-manager' ),
		__NAMESPACE__ . '\byline_meta_box',
		get_supported_post_types()
	);
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_meta_boxes' );

/**
 * Output the byline meta box.
 *
 * @param \WP_Post $post Post object.
 */
function byline_meta_box( $post ) {
	echo '<div id="byline-manager-metabox-root"></div>';
}
