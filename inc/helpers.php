<?php
/**
 * Assorted Helpers
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Get an array of post types that use this plugin to manage bylines.
 *
 * @return array Post types.
 */
function get_supported_post_types() {
	$post_types = get_post_types_by_support( 'author' );

	/**
	 * Filter the list of supported post types. Defaults to all post types
	 * supporting 'author'.
	 *
	 * @param array $post_types Post types with which to use Byline Manager.
	 */
	return apply_filters( 'byline_manager_supported_post_types', $post_types );
}
