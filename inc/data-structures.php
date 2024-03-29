<?php
/**
 * Data Structures
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use WP_Post;

// The profile post type slug.
const PROFILE_POST_TYPE = 'profile';

// The byline taxonomy slug.
const BYLINE_TAXONOMY = 'byline';

/**
 * Create the profile post type.
 */
function register_profile(): void {
	global $wp_rewrite;

	register_post_type( // phpcs:ignore WordPress.NamingConventions.ValidPostTypeSlug.NotStringLiteral
		PROFILE_POST_TYPE,
		[
			'labels'              => [
				'name'                     => __( 'Profiles', 'byline-manager' ),
				'singular_name'            => __( 'Profile', 'byline-manager' ),
				'add_new'                  => __( 'Add New Profile', 'byline-manager' ),
				'add_new_item'             => __( 'Add New Profile', 'byline-manager' ),
				'edit_item'                => __( 'Edit Profile', 'byline-manager' ),
				'new_item'                 => __( 'New Profile', 'byline-manager' ),
				'view_item'                => __( 'View Profile', 'byline-manager' ),
				'view_items'               => __( 'View Profiles', 'byline-manager' ),
				'search_items'             => __( 'Search Profiles', 'byline-manager' ),
				'not_found'                => __( 'No profiles found', 'byline-manager' ),
				'not_found_in_trash'       => __( 'No profiles found in Trash', 'byline-manager' ),
				'parent_item_colon'        => __( 'Parent Profile:', 'byline-manager' ),
				'all_items'                => __( 'All Profiles', 'byline-manager' ),
				'archives'                 => __( 'Profile Archives', 'byline-manager' ),
				'attributes'               => __( 'Profile Attributes', 'byline-manager' ),
				'insert_into_item'         => __( 'Insert into profile', 'byline-manager' ),
				'uploaded_to_this_item'    => __( 'Uploaded to this profile', 'byline-manager' ),
				'featured_image'           => __( 'Profile Photo', 'byline-manager' ),
				'set_featured_image'       => __( 'Set profile photo', 'byline-manager' ),
				'remove_featured_image'    => __( 'Remove profile photo', 'byline-manager' ),
				'use_featured_image'       => __( 'Use as profile photo', 'byline-manager' ),
				'filter_items_list'        => __( 'Filter profiles list', 'byline-manager' ),
				'items_list_navigation'    => __( 'Profiles list navigation', 'byline-manager' ),
				'items_list'               => __( 'Profiles list', 'byline-manager' ),
				'item_published'           => __( 'Profile published.', 'byline-manager' ),
				'item_published_privately' => __( 'Profile published privately.', 'byline-manager' ),
				'item_reverted_to_draft'   => __( 'Profile reverted to draft.', 'byline-manager' ),
				'item_scheduled'           => __( 'Profile scheduled.', 'byline-manager' ),
				'item_updated'             => __( 'Profile updated.', 'byline-manager' ),
				'menu_name'                => __( 'Profiles', 'byline-manager' ),
			],
			'public'              => true,
			'taxonomies'          => [ 'byline' ],
			'exclude_from_search' => true,
			'has_archive'         => true,
			'rewrite'             => [
				/**
				 * Filters the rewrite slug of the profile post type.
				 *
				 * @param string $slug The rewrite slug. Default is the default
				 *                     base for the author permalink structure.
				 */
				'slug'    => apply_filters( 'byline_manager_rewrite_slug', $wp_rewrite->author_base ),
				'feeds'   => true,
				'pages'   => true,
				'ep_mask' => EP_AUTHORS,
			],
			'capability_type'     => 'post',
			'menu_icon'           => 'dashicons-id',
			'menu_position'       => 71,
			'supports'            => [ 'title', 'editor', 'revisions', 'thumbnail', 'custom-fields' ],
			'show_in_rest'        => true,
			// Include Profiles in GraphQL.
			'show_in_graphql'     => true,
			'graphql_single_name' => 'profile',
			'graphql_plural_name' => 'profiles',
		]
	);
}
add_action( 'init', __NAMESPACE__ . '\register_profile' );

/**
 * Create the hidden byline taxonomy.
 */
function register_byline(): void {
	register_taxonomy(
		BYLINE_TAXONOMY,
		Utils::get_supported_post_types(),
		[
			'public'       => false,
			'sort'         => true,
			'rewrite'      => false,
			'show_in_rest' => true,
			'capabilities' => [
				'manage_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'edit_posts',
			],
			'labels'       => [
				'name'          => _x( 'Bylines', 'byline-manager' ),
				'singular_name' => _x( 'Byline', 'byline-manager' ),
				'search_items'  => __( 'Search Byline', 'byline-manager' ),
				'popular_items' => __( 'Popular Bylines', 'byline-manager' ),
				'all_items'     => __( 'All Bylines', 'byline-manager' ),
				'edit_item'     => __( 'Edit Byline', 'byline-manager' ),
				'view_item'     => __( 'View Byline', 'byline-manager' ),
				'update_item'   => __( 'Update Byline', 'byline-manager' ),
				'add_new_item'  => __( 'Add New Byline', 'byline-manager' ),
				'new_item_name' => __( 'New Byline Name', 'byline-manager' ),
			],
		],
	);
}
add_action( 'init', __NAMESPACE__ . '\register_byline', 10000 );

/**
 * Create or update byline terms when profile posts get updated.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated or not.
 */
function sync_profiles_and_bylines( $post_id, $post, $update ): void {
	if ( PROFILE_POST_TYPE !== $post->post_type ) {
		return;
	}

	// We may at some point want to make updates to the terms.
	if ( $update ) {
		return;
	}

	$result = wp_insert_term( "profile-{$post_id}", BYLINE_TAXONOMY );
	if ( is_wp_error( $result ) || empty( $result['term_id'] ) ) {
		update_post_meta( $post_id, 'byline_id', 'error' );
	} else {
		update_post_meta( $post_id, 'byline_id', $result['term_id'] );
	}
}
add_action( 'save_post', __NAMESPACE__ . '\sync_profiles_and_bylines', 100, 3 );

/**
 * When a profile post is deleted, also delete the associated byline term.
 *
 * @param int $post_id Post ID.
 */
function delete_byline_by_profile_id( $post_id ): void {
	if ( PROFILE_POST_TYPE !== get_post_type( $post_id ) ) {
		return;
	}

	$term_id = absint( get_post_meta( $post_id, 'byline_id', true ) );
	if ( $term_id ) {
		// Attempt to delete the term.
		wp_delete_term( $term_id, BYLINE_TAXONOMY );
	}
}
add_action( 'before_delete_post', __NAMESPACE__ . '\delete_byline_by_profile_id' );

/**
 * When a profile post is deleted, also delete the associated user.
 *
 * @param int $post_id Post ID.
 */
function delete_profile_by_associated_user( $post_id ): void {
	if ( PROFILE_POST_TYPE !== get_post_type( $post_id ) ) {
		return;
	}

	// Delete metas linking this profile to a user account, if any.
	$user_id = absint( get_post_meta( $post_id, 'user_id', true ) );
	if ( $user_id ) {
		delete_post_meta( $post_id, 'user_id', $user_id );
		delete_user_meta( $user_id, 'profile_id', $post_id );
	}
}
add_action( 'before_delete_post', __NAMESPACE__ . '\delete_profile_by_associated_user' );

/**
 * When a user is deleted, also delete the associated profile.
 *
 * @param int $user_id User ID.
 */
function delete_user_by_associated_profile( $user_id ): void {
	$profile_id = absint( get_user_meta( $user_id, 'profile_id', true ) );
	if ( $profile_id ) {
		delete_post_meta( $profile_id, 'user_id', $user_id );
		delete_user_meta( $user_id, 'profile_id', $profile_id );
	}
}
add_action( 'delete_user', __NAMESPACE__ . '\delete_user_by_associated_profile' );

/**
 * Set the title field placeholder text on profile posts.
 *
 * @param string  $title Placeholder text.
 * @param WP_Post $post  Post object.
 * @return string If $post is a profile, the new placeholder text. Else $text.
 */
function profile_post_title_placeholder( $title, $post ): string {
	if ( PROFILE_POST_TYPE === $post->post_type ) {
		return __( 'Display Name', 'byline-manager' );
	}
	return $title;
}
add_filter( 'enter_title_here', __NAMESPACE__ . '\profile_post_title_placeholder', 10, 2 );
