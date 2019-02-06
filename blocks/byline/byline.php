<?php
/**
 * Block Initializer
 * Functions to register client-side assets (scripts and stylesheets) for the Byline block.
 *
 * @package Byline
 */

namespace Byline_Manager;

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type/#enqueuing-block-scripts
 */
function byline_init() {
	// Only load if Gutenberg is available.
	if ( ! function_exists( '\register_block_type' ) ) {
		return;
	}

	$dev_mode = ! empty( $_GET['byline-dev'] ); // wpcs: csrf ok, input var ok.

	// Script assets used in the editor.
	$script_url = $dev_mode
		? '//localhost:8080/bylinedev.bundle.js'
		: get_asset_uri( 'blocks.js' );
	wp_register_script(
		'byline-editor-js',
		$script_url,
		[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ],
		'1.0.0',
		true
	);

	// Editor-only styles. These are loaded with the bundle in dev mode.
	if ( ! $dev_mode ) {
		wp_register_style(
			'byline-editor-css',
			get_asset_uri( 'blocks-style.css' ),
			[],
			'1.0.0'
		);
	}

	register_block_type(
		'dj/byline',
		[
			'editor_script' => 'byline-editor-js',
			'editor_style'  => 'byline-editor-css',
		]
	);
}
add_action( 'init', __NAMESPACE__ . '\byline_init' );
