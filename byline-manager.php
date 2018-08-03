<?php
/**
 * Plugin Name:     Byline Manager
 * Plugin URI:      https://github.com/alleyinteractive/byline-manager
 * Description:     Manage an article's byline and author profiles
 * Author:          Alley Interactive
 * Text Domain:     byline-manager
 * Domain Path:     /languages
 * Version:         0.1.0
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

// Asset loader.
require_once PATH . 'inc/assets.php';

// Data structures.
require_once PATH . 'inc/data-structures.php';
