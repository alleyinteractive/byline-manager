<?php
/**
 * Static assets loaders for this plugin
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

define( __NAMESPACE__ . '\BUILD_URL', URL . 'client/build/' );
define( __NAMESPACE__ . '\BUILD_PATH', PATH . 'client/build/' );

/**
 * Enqueue basic UI admin scripts and styles.
 *
 * @param string $hook Page suffix.
 */
function admin_enqueue_scripts( $hook ) {
	if (
		in_array( $hook, [ 'post-new.php', 'post.php' ], true )
		&& in_array( get_post_type(), get_supported_post_types(), true )
	) {
		if ( ! empty( $_GET['bm-dev'] ) ) {
			wp_enqueue_script( 'byline-manager-js', '//localhost:8080/dev.bundle.js', [], '0.1.0', true );
		} else {
			wp_enqueue_script( 'byline-manager-js', get_asset_uri( 'main.js' ), [], '0.1.0', true );
			wp_enqueue_style( 'byline-manager-css', get_asset_uri( 'main.css' ), [], '0.1.0' );
		}

		wp_localize_script( 'byline-manager-js', 'bylineData', [
			'addAuthorPlaceholder' => __( 'Search for an author to add to the byline', 'byline-manager' ),
			'removeAuthorLabel'    => __( 'Remove author from byline', 'byline-manager' ),
			'apiUrl'               => home_url( '/wp-json/byline-manager/v1/authors' ),
			'nonce'                => wp_create_nonce( 'set_byline_data' ),
			'profiles'             => array_map(
				__NAMESPACE__ . '\get_profile_data',
				get_profiles_for_post()
			),
		] );
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts' );

/**
 * Attempt to load a file at the specified path and parse its contents as JSON.
 *
 * @param string $path The path to the JSON file to load.
 * @return array|null;
 */
function load_asset_file( $path ) {
	if ( ! file_exists( $path ) ) {
		return null;
	}
	$contents = file_get_contents( $path );
	if ( empty( $contents ) ) {
		return null;
	}
	return json_decode( $contents, true );
}

/**
 * Load the build asset manifest file, and attempt to decode and return the
 * asset list JSON if found.
 *
 * @return array
 */
function get_assets_list() {
	static $assets;
	if ( ! isset( $assets ) ) {
		$assets = load_asset_file( BUILD_PATH . 'asset-manifest.json' );
		if ( empty( $assets ) ) {
			$assets = [];
		}
	}
	return $assets;
}

/**
 * Return web URIs or convert relative filesystem paths to absolute paths.
 *
 * @param string $file Name of the file in the asset manifest.
 * @return string
 */
function get_asset_uri( $file ) {
	$assets_list = get_assets_list();
	if ( ! empty( $assets_list[ $file ] ) ) {
		return BUILD_URL . $assets_list[ $file ];
	}
}
