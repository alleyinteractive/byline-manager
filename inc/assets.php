<?php
/**
 * Static assets loaders for this plugin
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;

define( 'BYLINE_MANAGER_ASSET_MAP', read_asset_map( dirname( __DIR__ ) . '/client/build/assetMap.json' ) );
define( 'BYLINE_MANAGER_ASSET_MODE', BYLINE_MANAGER_ASSET_MAP['mode'] ?? 'production' );

/**
 * Enqueue basic UI admin scripts and styles.
 *
 * @param string $hook Page suffix.
 */
function admin_enqueue_scripts( $hook ): void {
	if (
		in_array( $hook, [ 'post-new.php', 'post.php' ], true )
		&& (
			Utils::is_post_type_supported()
			|| PROFILE_POST_TYPE === get_post_type()
		)
	) {
		wp_enqueue_style( 'byline-manager-css', get_asset_path( 'main.css' ), [], '0.1.0' );

		wp_enqueue_script(
			'byline-manager-js',
			get_asset_path( 'main.js' ),
			get_asset_dependencies( 'main.php' ),
			get_asset_hash( 'main.js' ),
			true
		);

		// Add Byline data for script.
		localize_admin_script( 'byline-manager-js' );
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts' );

/**
 * A callback for the enqueue_block_editor_assets action hook to register assets
 * for the Gutenberg editor.
 *
 * @since 1.0.0
 */
function action_enqueue_block_editor_assets(): void {
	// Only enqueue the script to register the scripts if supported.
	if ( ! Utils::is_post_type_supported() ) {
		return;
	}

	wp_enqueue_script(
		'byline-manager-block-editor-js',
		get_asset_path( 'blockEditor.js' ),
		get_asset_dependencies( 'blockEditor.php' ),
		get_asset_hash( 'blockEditor.js' ),
		true
	);

	// Add Byline data for script.
	localize_admin_script( 'byline-manager-block-editor-js' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\action_enqueue_block_editor_assets' );

/**
 * Localizes an admin script with Byline data.
 *
 * @param string $handle The script handle.
 */
function localize_admin_script( $handle ): void {
	// Build the byline metabox data.
	$byline_metabox_data = Utils::get_byline_meta_for_post();
	if ( ! empty( $byline_metabox_data['profiles'] ) ) {
		$profiles = [];
		$index    = 0;
		foreach ( $byline_metabox_data['profiles'] as $entry ) {
			if (
				! empty( $entry['type'] )
				&& 'byline_id' === $entry['type']
				&& ! empty( $entry['atts']['post_id'] )
			) {
				// Handle byline profile ID entries.
				$profile = Profile::get_by_post( $entry['atts']['post_id'] );
				if ( $profile instanceof Profile ) {
					$profiles[] = get_profile_data_for_meta_box( $profile );
				}
			} elseif ( ! empty( $entry['atts']['text'] ) ) {
				// Handle text-only bylines.
				$text_profile = TextProfile::create( $entry['atts'] );
				$profiles[]   = [
					// Uses a semi-arbitrary ID to give the script a reference point.
					'id'   => $text_profile->id,
					'name' => $text_profile->display_name,
				];
			}
			$index++;
		}
		$byline_metabox_data['profiles'] = $profiles;
	}

	$byline_localized_data = [
		'addAuthorLabel'         => __( 'Search for an author', 'byline-manager' ),
		'addAuthorPlaceholder'   => __( 'Enter name', 'byline-manager' ),
		'removeAuthorLabel'      => __( 'Remove author from byline', 'byline-manager' ),
		'addFreeformLabel'       => __( 'Enter text to add to the byline', 'byline-manager' ),
		'addFreeformPlaceholder' => __( 'Enter text', 'byline-manager' ),
		'addFreeformButtonLabel' => __( 'Insert', 'byline-manager' ),
		'linkUserPlaceholder'    => __( 'Search for a user account by name', 'byline-manager' ),
		'userAlreadyLinked'      => __( 'This user is linked to another profile', 'byline-manager' ),
		'linkedToLabel'          => __( 'Linked to:', 'byline-manager' ),
		'unlinkLabel'            => __( 'Unlink', 'byline-manager' ),
		'profilesApiUrl'         => rest_url( '/byline-manager/v1/authors' ),
		'usersApiUrl'            => rest_url( '/byline-manager/v1/users' ),
		'postId'                 => get_the_ID(),
		'bylineMetaBox'          => $byline_metabox_data,
	];

	/**
	 * Filters the localized data for Byline Manager.
	 *
	 * @param array $byline_metabox_data Byline Manager localized data.
	 */
	$byline_localized_data = apply_filters( 'byline_manager_localized_data', $byline_localized_data );

	wp_localize_script( $handle, 'bylineData', $byline_localized_data );
}

/**
 * Get the contentHash for a given asset.
 *
 * @param string $asset Entry point and asset type separated by a '.'.
 *
 * @return string The asset's hash.
 */
function get_asset_hash( string $asset ) : string {
	return get_asset_property( $asset, 'hash' )
		?? BYLINE_MANAGER_ASSET_MAP['hash']
		?? '1.0.0';
}

/**
 * Gets asset dependencies from the generated asset manifest.
 *
 * @param string $asset Entry point and asset type separated by a '.'.
 *
 * @return array An array of dependencies for this asset.
 */
function get_asset_dependencies( string $asset ) : array {
	// Get the path to the PHP file containing the dependencies.
	$dependency_file = get_asset_path( $asset, true );
	if ( empty( $dependency_file ) ) {
		return [];
	}

	// Ensure the filepath is valid.
	if ( ! validate_path( $dependency_file ) ) {
		return [];
	}

	// Try to load the dependencies.
	// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	$dependencies = require $dependency_file;
	if ( empty( $dependencies['dependencies'] ) || ! is_array( $dependencies['dependencies'] ) ) {
		return [];
	}

	return $dependencies['dependencies'];
}

/**
 * Get the URL for a given asset.
 *
 * @param string  $asset Entry point and asset type separated by a '.'.
 * @param boolean $dir   Optional. Whether to return the directory path or the plugin URL path. Defaults to false (returns URL).
 *
 * @return string The asset URL.
 */
function get_asset_path( string $asset, bool $dir = false ) : string {
	// Try to get the relative path.
	$relative_path = get_asset_property( $asset, 'path' );
	if ( empty( $relative_path ) ) {
		return '';
	}

	// Negotiate the base path.
	$base_path = true === $dir
		? dirname( __DIR__ ) . '/client/build'
		: plugins_url( 'client/build', __DIR__ );

	return trailingslashit( $base_path ) . $relative_path;
}

/**
 * Get a property for a given asset.
 *
 * @param string $asset Entry point and asset type separated by a '.'.
 * @param string $prop The property to get from the entry object.
 * @return string|null The asset property based on entry and type.
 */
function get_asset_property( string $asset, string $prop ) : ?string {
	/*
	 * Appending a '.' ensures the explode() doesn't generate a notice while
	 * allowing the variable names to be more readable via list().
	 */
	list( $entrypoint, $type ) = explode( '.', "$asset." );

	$asset_property = BYLINE_MANAGER_ASSET_MAP[ $entrypoint ][ $type ][ $prop ] ?? null;

	return $asset_property ? $asset_property : null;
}

/**
 * Decode the asset map at the given file path.
 *
 * @param string $path File path.
 * @return array The asset map.
 */
function read_asset_map( string $path ) : array {
	if ( validate_path( $path ) ) {
		ob_start();
		include $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.IncludingFile, WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		return json_decode( ob_get_clean(), true );
	}

	return [];
}
