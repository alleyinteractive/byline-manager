<?php
/**
 * Plugin Name:     Byline Manager
 * Plugin URI:      https://github.com/alleyinteractive/byline-manager
 * Description:     Manage an article's byline and author profiles.
 * Author:          Alley Interactive
 * Author URI:      https://alley.com
 * Text Domain:     byline-manager
 * Domain Path:     /languages
 * Version:         0.4.0
 * Requires WP:     5.9
 * Requires PHP:    8.0
 * Tested up to:    6.4
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BYLINE_MANAGER_PATH', __DIR__ . '/' );

/**
 * Helper function to validate a path before doing something with it.
 *
 * @param string $path The path to validate.
 *
 * @return bool True if the path is valid, false otherwise.
 */
function validate_path( string $path ) : bool {
	return in_array( validate_file( $path ), [ 0, 2 ], true ) && file_exists( $path );
}

// Autoloader.
require_once BYLINE_MANAGER_PATH . 'inc/autoload.php';

// Template Tags.
require_once BYLINE_MANAGER_PATH . 'inc/template-tags.php';

// Asset loader.
require_once BYLINE_MANAGER_PATH . 'inc/assets.php';

// Data structures.
require_once BYLINE_MANAGER_PATH . 'inc/data-structures.php';

// Admin interfaces.
require_once BYLINE_MANAGER_PATH . 'inc/admin-ui.php';

// REST API integration.
require_once BYLINE_MANAGER_PATH . 'inc/rest-api.php';

// WPGraphQL integration.
require_once BYLINE_MANAGER_PATH . 'inc/graphql.php';

// Yoast SEO integration.
require_once BYLINE_MANAGER_PATH . 'inc/yoast.php';

// Hook into core filters to output byline.
require_once BYLINE_MANAGER_PATH . 'inc/core-filters.php';

require_once BYLINE_MANAGER_PATH . 'inc/class-core-author-block.php';
