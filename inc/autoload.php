<?php
/**
 * This file sets up the custom autoloader
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Autoload classes.
 *
 * @param  string $cls Class name.
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'Byline_Manager\\' ) !== 0 ) {
		return;
	}

	$cls  = strtolower( str_replace( [ 'Byline_Manager\\', '_' ], [ '', '-' ], $cls ) );
	$dirs = explode( '\\', $cls );
	$cls  = array_pop( $dirs );

	require_once PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . '/class-' . $cls . '.php';
}
spl_autoload_register( '\Byline_Manager\autoload' );
