<?php
/**
 * Data Structures
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

// The profile post type slug.
const PROFILE_POST_TYPE = 'profile';

// The byline taxonomy slug.
const BYLINE_TAXONOMY = 'byline';

/**
 * Create the profile post type.
 */
function register_profile() {
	register_post_type(
		PROFILE_POST_TYPE,
		[
			'labels'              => [
				'name'               => __( 'Profiles', 'byline-manager' ),
				'singular_name'      => __( 'Profile', 'byline-manager' ),
				'add_new'            => __( 'Add New Profile', 'byline-manager' ),
				'add_new_item'       => __( 'Add New Profile', 'byline-manager' ),
				'edit_item'          => __( 'Edit Profile', 'byline-manager' ),
				'new_item'           => __( 'New Profile', 'byline-manager' ),
				'view_item'          => __( 'View Profile', 'byline-manager' ),
				'search_items'       => __( 'Search Profiles', 'byline-manager' ),
				'not_found'          => __( 'No Profiles found', 'byline-manager' ),
				'not_found_in_trash' => __( 'No Profiles found in Trash', 'byline-manager' ),
				'parent_item_colon'  => __( 'Parent Profile:', 'byline-manager' ),
				'menu_name'          => __( 'Profiles', 'byline-manager' ),
			],
			'public'              => true,
			'taxonomies'          => [ 'byline' ],
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => null,
			'menu_icon'           => 'dashicons-id',
			'menu_position'       => 71,
			'supports'            => [ 'title', 'editor', 'revisions', 'thumbnail' ],
		]
	);
}
add_action( 'init', __NAMESPACE__ . '\register_profile' );

/**
 * Create the hidden byline taxonomy.
 */
function register_byline() {
	register_taxonomy(
		BYLINE_TAXONOMY,
		Utils::get_supported_post_types(),
		[
			'public'       => false,
			'sort'         => true,
			'capabilities' => [
				'manage_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'do_not_allow',
			],
		]
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
function sync_profiles_and_bylines( $post_id, $post, $update ) {
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
function delete_byline_by_profile_id( $post_id ) {
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
 * Set the title field placeholder text on profile posts.
 *
 * @param string   $title Placeholder text.
 * @param \WP_Post $post  Post object.
 * @return string If $post is a profile, the new placeholder text. Else $text.
 */
function profile_post_title_placeholder( $title, $post ) {
	if ( PROFILE_POST_TYPE === $post->post_type ) {
		return __( 'Display Name', 'byline-manager' );
	}
	return $title;
}
add_filter( 'enter_title_here', __NAMESPACE__ . '\profile_post_title_placeholder', 10, 2 );
