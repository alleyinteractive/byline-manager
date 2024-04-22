<?php
/**
 * Admin Interfaces
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use WP_Screen;
use WP_Post;
use WP_User;
use stdClass;

/**
 * Register meta boxes used by the plugin.
 */
function register_meta_boxes(): void {
	$supported_post_types = Utils::get_supported_post_types();

	add_meta_box(
		'byline-manager-user-link-meta-box',
		__( 'User Account', 'byline-manager' ),
		__NAMESPACE__ . '\user_link_meta_box',
		PROFILE_POST_TYPE,
		'side'
	);

	// Only load within the classic editor.
	$current_screen = get_current_screen();

	if (
		$current_screen instanceof WP_Screen
		&& $current_screen->is_block_editor()
	) {
		return;
	}

	if ( $supported_post_types ) {
		add_meta_box(
			'byline-manager-byline-meta-box',
			__( 'Byline', 'byline-manager' ),
			__NAMESPACE__ . '\byline_meta_box',
			$supported_post_types
		);
	}
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_meta_boxes' );

/**
 * Output the byline meta box.
 */
function byline_meta_box(): void {
	wp_nonce_field( 'set_byline_data', 'post_byline_nonce' );
	echo '<div id="byline-manager-metabox-root"></div>';
}

/**
 * Output the user link meta box.
 *
 * @param WP_Post $post Post object.
 */
function user_link_meta_box( $post ): void {
	$stored_id = absint( get_post_meta( $post->ID, 'user_id', true ) );

	if ( ! empty( $stored_id ) ) {
		$user = get_user_by( 'id', $stored_id );
	} else {
		$user = false;
	}

	if ( $user instanceof WP_User ) {
		$userdata = get_user_data_for_meta_box( $user );
	} else {
		// Use stdClass so the JSON gets written as an empty object.
		$userdata = new stdClass();
	}

	wp_nonce_field( 'set_user_link', 'profile_user_link_nonce' );
	printf(
		'<div id="byline-manager-user-link-root" data-user="%s"></div>',
		esc_attr( wp_json_encode( $userdata ) )
	);
}

/**
 * Given a profile post object, build the necessary data needed by the meta box.
 *
 * @param Profile $profile Profile object.
 * @return array {
 *     Necessary data to build the meta box.
 *
 *     @type int    $id        Post ID.
 *     @type int    $byline_id Term ID.
 *     @type string $name      Profile name (author name).
 *     @type string $image     URL for profile's image.
 * }
 */
function get_profile_data_for_meta_box( Profile $profile ): array {
	$profile_data = [
		'id'        => $profile->post_id,
		'byline_id' => absint( get_post_meta( $profile->post_id, 'byline_id', true ) ),
		'name'      => $profile->display_name,
		'image'     => get_the_post_thumbnail_url( $profile->post_id, [ 50, 50 ] ),
	];

	/**
	 * Filters the profile data sent to the metabox.
	 *
	 * @param array   $profile_data Profile details.
	 * @param Profile $profile      Profile object.
	 */
	return apply_filters(
		'byline_manager_get_profile_data_for_meta_box',
		$profile_data,
		$profile
	);
}

/**
 * Given a user object, build the data needed by the user link meta box.
 *
 * @param WP_User  $user            User object.
 * @param int|null $current_post_id Current post ID, if applicable.
 * @return array {
 *     Necessary data to build the meta box.
 *
 *     @type int    $id    User ID.
 *     @type string $name  Display Name.
 * }
 */
function get_user_data_for_meta_box( WP_User $user, $current_post_id = null ): array {
	$linked_id = absint( get_user_meta( $user->ID, 'profile_id', true ) );
	return [
		'id'     => $user->ID,
		'name'   => $user->display_name,
		'linked' => boolval( $linked_id ) && $linked_id !== $current_post_id,
	];
}

/**
 * Set the byline when a post is saved.
 *
 * @param int     $post_id Post ID being saved.
 * @param WP_Post $post    Post object being saved.
 */
function set_byline( $post_id, $post ): void {
	// Don't set bylines on autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Only proceed for permitted post types.
	if ( ! Utils::is_post_type_supported( $post->post_type ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if (
		empty( $_POST['post_byline_nonce'] )
		|| ! wp_verify_nonce( $_POST['post_byline_nonce'], 'set_byline_data' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	) {
		return;
	}

	$meta = [
		'byline_entries' => [],
	];
	// The data from this array is sanitized as it's used.
	$byline_entries = isset( $_POST['byline_entry'] ) ? wp_unslash( $_POST['byline_entry'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

	if ( ! empty( $byline_entries ) && is_array( $byline_entries ) ) {
		foreach ( $byline_entries as $entry ) {
			// Don't save empty items.
			if ( empty( $entry['type'] ) || empty( $entry['value'] ) ) {
				continue;
			}

			if ( 'text' === $entry['type'] ) {
				$meta['byline_entries'][] = [
					'type' => 'text',
					'atts' => [
						'text' => wp_kses_post( wp_unslash( $entry['value'] ) ),
					],
				];
			} elseif ( 'byline_id' === $entry['type'] ) {
				$meta['byline_entries'][] = [
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => absint( $entry['value'] ),
					],
				];
			}
		}
	}

	// Set the byline.
	Utils::set_post_byline( $post_id, $meta );
}
add_action( 'save_post', __NAMESPACE__ . '\set_byline', 10, 2 );

/**
 * Set the user link when a profile post is saved.
 *
 * @param int     $post_id Post ID being saved.
 * @param WP_Post $post    Post object being saved.
 */
function set_profile_user_link( $post_id, $post ): void {
	// Don't set user link on autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Only proceed for the profile post type.
	if ( PROFILE_POST_TYPE !== $post->post_type ) {
		return;
	}

	// Verify that the nonce is valid.
	if (
		empty( $_POST['profile_user_link_nonce'] )
		|| ! wp_verify_nonce( $_POST['profile_user_link_nonce'], 'set_user_link' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	) {
		return;
	}

	$new_user_id = ! empty( $_POST['profile_user_link'] )
		? absint( $_POST['profile_user_link'] )
		: 0;

	if ( ! wp_is_post_revision( $post ) ) {
		$profile = Profile::get_by_post( $post );
		$profile->update_user_link( $new_user_id );
	}
}
add_action( 'save_post', __NAMESPACE__ . '\set_profile_user_link', 10, 2 );

/**
 * Add the posts column to the profiles list.
 *
 * @param array $columns Columns.
 * @return array
 */
function add_posts_column( $columns ): array {
	return array_merge(
		$columns,
		[
			'posts' => __( 'Posts', 'byline-manager' ),
		]
	);
}
add_filter( 'manage_profile_posts_columns', __NAMESPACE__ . '\add_posts_column' );

/**
 * Render the content for the posts column.
 *
 * @param string $column  Column slug.
 * @param int    $post_id Post ID.
 */
function render_posts_column( $column, $post_id ): void {
	if ( 'posts' === $column ) {
		$term     = get_term_by( 'slug', 'profile-' . $post_id, BYLINE_TAXONOMY );
		$numposts = $term->count;
		if ( 1 === $numposts ) {
			$post_count_str = __( 'One post by this author', 'byline-manager' );
		} else {
			// translators: %s: Number of posts.
			$post_count_str = sprintf( _n( '%s post by this author', '%s posts by this author', (int) $numposts, 'byline-manager' ), $numposts );
		}
		if ( $numposts > 0 ) {
			printf(
				'<a href="edit.php?byline=profile-%1$s" class="edit"><span aria-hidden="true">%2$s</span><span class="screen-reader-text">%3$s</span></a>',
				(int) $post_id,
				(int) $numposts,
				esc_html( $post_count_str )
			);
		} else {
			printf(
				'<span aria-hidden="true">%1$s</span><span class="screen-reader-text">%2$s</span>',
				(int) $numposts,
				esc_html( $post_count_str )
			);
		}
	}
}
add_action( 'manage_profile_posts_custom_column', __NAMESPACE__ . '\render_posts_column', 10, 2 );

/**
 * Remove the author support from post types.
 * This is done to prevent the author dropdown from being displayed in the post editor.
 */
function remove_author_support() {
	/**
	 * Filter the list of post types that should not have author support. Defaults to all public post types.
	 *
	 * @param string[] $post_types Post types with author support removed.
	 */
	$post_types = apply_filters( 'byline_manager_remove_author_support', get_post_types( [ 'public' => true ], 'names' ) );

	// Remove author support from the provided post types.
	foreach ( $post_types as $post_type ) {
		remove_post_type_support( $post_type, 'author' );
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\remove_author_support' );
