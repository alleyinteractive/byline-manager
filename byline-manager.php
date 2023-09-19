<?php
/**
 * Plugin Name:     Byline Manager
 * Plugin URI:      https://github.com/alleyinteractive/byline-manager
 * Description:     Manage an article's byline and author profiles.
 * Author:          Alley Interactive
 * Author URI:      https://alley.com
 * Text Domain:     byline-manager
 * Domain Path:     /languages
 * Version: 0.2.3
 * Requires WP:     5.9
 * Requires PHP:    8.0
 * Tested up to:    6.3
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BYLINE_MANAGER_PATH', __DIR__ . '/' );

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

// REST API interfaces.
require_once BYLINE_MANAGER_PATH . 'inc/rest-api.php';

// GraphQL interfaces.
require_once BYLINE_MANAGER_PATH . 'inc/graphql.php';

// Hook into core filters to output byline.
require_once BYLINE_MANAGER_PATH . 'inc/core-filters.php';
