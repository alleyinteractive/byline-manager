<?php
/**
 * Representation of an individual profile
 *
 * @package Byline_Manager
 */

namespace Byline_Manager\Models;

use const Byline_Manager\PROFILE_POST_TYPE;

/**
 * Representation of an individual profile.
 */
class Profile {

	/**
	 * Profile post object.
	 *
	 * @var int
	 */
	protected $post;

	/**
	 * ID for the correlated byline term.
	 *
	 * @var int
	 */
	protected $term_id;

	/**
	 * Create a new Profile object.
	 *
	 * @param array $args Arguments with which to create the new object.
	 * @return Profile|WP_Error
	 */
	public static function create( $args ) {
		if ( empty( $args['post_title'] ) ) {
			return new \WP_Error( 'missing-post_title', __( "'post_title' (user's display name) is a required argument", 'byline-manager' ) );
		}

		// Set profile defaults.
		$args = wp_parse_args(
			$args,
			[
				'post_type'   => PROFILE_POST_TYPE,
				'post_status' => 'publish',
			]
		);

		$post_id = wp_insert_post( $args, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		return self::get_by_post( $post_id );
	}

	/**
	 * Create a new Profile object from an existing WordPress user.
	 *
	 * @param WP_User|int $user WordPress user to clone.
	 * @return Profile|WP_Error
	 */
	public static function create_from_user( $user ) {
		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}
		if ( ! is_a( $user, 'WP_User' ) ) {
			return new \WP_Error( 'missing-user', __( "User doesn't exist", 'byline-manager' ) );
		}
		$existing = self::get_by_user_id( $user->ID );
		if ( $existing ) {
			return new \WP_Error( 'existing-profile', __( 'User already has a profile.', 'byline-manager' ) );
		}
		$profile = self::create(
			[
				'post_title'   => $user->display_name,
				'post_name'    => $user->user_nicename,
				'post_content' => $user->description,
			]
		);
		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		// Clone applicable user fields.
		$user_fields = array(
			'first_name',
			'last_name',
			'user_email',
			'user_login',
			'user_url',
		);
		update_post_meta( $profile->post->ID, 'user_id', $user->ID );
		foreach ( $user_fields as $field ) {
			update_post_meta( $profile->post->ID, $field, $user->$field );
		}
		return $profile;
	}

	/**
	 * Get a profile object based on its term id.
	 *
	 * @param int|\WP_Post $post Post ID or object of a profile.
	 * @return Profile|false
	 */
	public static function get_by_post( $post ) {
		$post = get_post( $post );
		if ( $post && PROFILE_POST_TYPE === $post->post_type ) {
			return new Profile( $post );
		}
		return false;
	}

	/**
	 * Get a profile object based on its term id.
	 *
	 * @param int $term_id ID for the profile term.
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_term_id( $term_id ) {
		return false;
	}

	/**
	 * Get a profile object based on its post slug.
	 *
	 * @param string $slug Slug for the profile term.
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_slug( $slug ) {
		return false;
	}

	/**
	 * Get a profile object based on its user id.
	 *
	 * @param int $user_id ID for the profile's user.
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_user_id( $user_id ) {
		return false;
	}

	/**
	 * Instantiate a new Profile object
	 *
	 * Profiles are always fetched by static fetchers.
	 *
	 * @param int|\WP_Post $post Post ID or object of a profile.
	 */
	private function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Get an object attribute.
	 *
	 * @param string $attribute Attribute name.
	 * @return mixed
	 */
	public function __get( $attribute ) {
		// Underscore prefix means protected.
		if ( '_' === $attribute[0] ) {
			return null;
		}

		// Normalize byline_id and term_id.
		if ( 'byline_id' === $attribute ) {
			$attribute = 'term_id';
		}

		// If this is an existing attribute on the class, return it.
		if ( isset( $this->$attribute ) ) {
			return $this->$attribute;
		}

		// 'link' should use the profile link.
		if ( 'link' === $attribute ) {
			return get_permalink( $this->post_id );
		}

		// These fields are actually on the Post object.
		switch ( $attribute ) {
			case 'display_name':
				return $this->get_post()->post_title;
			case 'user_nicename':
				return $this->get_post()->post_name;
			case 'description':
				return $this->get_post()->post_content;
			case 'post_id':
				return $this->get_post()->ID;
		}

		// Term ID (and byline ID) can get cached in the object.
		if ( 'term_id' === $attribute ) {
			$this->term_id = absint( get_post_meta( $this->post_id, 'byline_id', true ) );
			return $this->term_id;
		}

		return get_post_meta( $this->post_id, $attribute, true );
	}

	/**
	 * Get the post object forthe profile.
	 *
	 * @return \WP_Post
	 */
	public function get_post() {
		if ( ! isset( $this->post ) ) {
			$this->post = get_post( $this->post_id );
		}

		return $this->post;
	}

	/**
	 * Update the user link for a profile, and reciprocal link in the user.
	 *
	 * @param int $new_user_id User ID to link. Set to 0 to unlink.
	 */
	public function update_user_link( $new_user_id ) {
		$post_id = $this->get_post()->ID;

		// First, check to see if this profile is linked, for reciprocal updates.
		$old_user_id = absint( get_post_meta( $post_id, 'user_id', true ) );
		if ( $old_user_id && $old_user_id !== $new_user_id ) {
			delete_user_meta( $old_user_id, 'profile_id' );
			delete_post_meta( $post_id, 'user_id' );
		}

		// Save the post meta.
		if ( ! empty( $new_user_id ) ) {
			update_post_meta( $post_id, 'user_id', $new_user_id );
			update_user_meta( $new_user_id, 'profile_id', $post_id );
		}
	}

	/**
	 * Get the linked user ID for the current profile.
	 *
	 * @return int User ID or 0 if this profile is not linked.
	 */
	public function get_linked_user_id() {
		return absint( get_post_meta( $this->get_post()->ID, 'user_id', true ) );
	}
}
