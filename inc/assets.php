<?php
/**
 * Static assets loaders for this plugin
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;

const BUILD_URL  = URL . 'client/build/';
const BUILD_PATH = PATH . 'client/build/';

/**
 * Enqueue basic UI admin scripts and styles.
 *
 * @param string $hook Page suffix.
 */
function admin_enqueue_scripts( $hook ) {
	if (
		in_array( $hook, [ 'post-new.php', 'post.php' ], true )
		&& (
			Utils::is_post_type_supported()
			|| PROFILE_POST_TYPE === get_post_type()
		)
	) {
		wp_enqueue_style( 'byline-manager-css', get_asset_uri( 'main.css' ), [], '0.1.0' );

		// Only load within the classic editor.
		$current_screen = get_current_screen();

		if (
			$current_screen instanceof \WP_Screen
			&& post_type_supports( $current_screen->post_type, 'custom-fields' )
			&& $current_screen->is_block_editor()
		) {
			return;
		}

		wp_enqueue_script( 'byline-manager-js', get_asset_uri( 'main.js' ), [], '0.1.0', true );

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
function action_enqueue_block_editor_assets() {
	// Only enqueue the script to register the scripts if supported.
	if ( ! Utils::is_post_type_supported() ) {
		return;
	}

	wp_enqueue_script( 'byline-manager-block-editor-js', get_asset_uri( 'blockEditor.js' ), [ 'wp-editor', 'wp-i18n', 'wp-blocks', 'wp-api-fetch', 'wp-components' ], '0.1.0', true );

	// Add Byline data for script.
	localize_admin_script( 'byline-manager-block-editor-js' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\action_enqueue_block_editor_assets' );

/**
 * Localizes an admin script with Byline data.
 *
 * @param  string $handle The script handle.
 */
function localize_admin_script( $handle ) {
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

	wp_localize_script(
		$handle,
		'bylineData',
		[
			'addAuthorLabel'         => __( 'Search for a byline author', 'byline-manager' ),
			'addAuthorPlaceholder'   => __( 'Enter name', 'byline-manager' ),
			'removeAuthorLabel'      => __( 'Remove author from byline', 'byline-manager' ),
			'addFreeformlabel'       => __( 'Enter text to add to the byline', 'byline-manager' ),
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
		]
	);
}

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
