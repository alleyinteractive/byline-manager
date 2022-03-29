<?php
/**
 * Plugin Name:     Byline Manager
 * Plugin URI:      https://github.com/alleyinteractive/byline-manager
 * Description:     Manage an article's byline and author profiles
 * Author:          Alley Interactive
 * Text Domain:     byline-manager
 * Domain Path:     /languages
 * Version:         0.2.0
 *
 * @package         Byline_Manager
 */

namespace Byline_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( __NAMESPACE__ . '\PATH', __DIR__ . '/' );
define( __NAMESPACE__ . '\URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Autoloader.
require_once PATH . 'inc/autoload.php';

// Template Tags.
require_once PATH . 'inc/template-tags.php';

// Asset loader.
require_once PATH . 'inc/assets.php';
require_once PATH . 'inc/asset-loader-bridge.php';

// Data structures.
require_once PATH . 'inc/data-structures.php';

// Admin interfaces.
require_once PATH . 'inc/admin-ui.php';

// REST API interfaces.
require_once PATH . 'inc/rest-api.php';

// Hook into core filters to output byline.
require_once PATH . 'inc/core-filters.php';
