<?php
/**
 * Representation of an individual profile
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager\Models;

use WP_Error;
use WP_Post;
use WP_User;

use const Byline_Manager\PROFILE_POST_TYPE;

/**
 * Representation of an individual profile.
 *
 * Dynamic properties.
 *
 * @property int    $byline_id    Term ID for the profile.
 * @property string $description  Profile description.
 * @property string $display_name Display name for the profile.
 * @property string $link         Profile permalink.
 * @property int    $post_id      Post ID for the profile.
 * @property int    $term_id      Term ID for the profile.
 * @property string $user_nicename User nicename.
 * @property string $user_url     User url.
 */
class Profile {
	/**
	 * Profile post object.
	 *
	 * @var WP_Post
	 */
	public WP_Post $post;

	/**
	 * Profile term ID.
	 *
	 * Use {@see Profile::get_term_id()} to access this value.
	 *
	 * @var int
	 */
	private int $_term_id; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Create a new Profile object.
	 *
	 * @param array $args Arguments with which to create the new object.
	 * @return Profile|WP_Error
	 */
	public static function create( array $args ): Profile|WP_Error {
		if ( empty( $args['post_title'] ) ) {
			return new WP_Error( 'missing-post_title', __( "'post_title' (user's display name) is a required argument", 'byline-manager' ) );
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
	public static function create_from_user( $user ): Profile|WP_Error {
		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}

		if ( ! is_a( $user, 'WP_User' ) ) {
			return new WP_Error( 'missing-user', __( "User doesn't exist", 'byline-manager' ) );
		}

		$existing = self::get_by_user_id( $user->ID );

		if ( $existing ) {
			return new WP_Error( 'existing-profile', __( 'User already has a profile.', 'byline-manager' ) );
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

		$profile->update_user_link( $user->ID );

		// Clone applicable user fields.
		$user_fields = [
			'first_name',
			'last_name',
			'user_email',
			'user_login',
			'user_url',
		];

		foreach ( $user_fields as $field ) {
			update_post_meta( $profile->post->ID, $field, $user->$field );
		}

		return $profile;
	}

	/**
	 * Get a profile object based on its profile post ID or object.
	 *
	 * @param int|WP_Post $post Post ID or object of a profile.
	 * @return Profile|false
	 */
	public static function get_by_post( $post ) {
		$post = get_post( $post );
		if ( $post && PROFILE_POST_TYPE === $post->post_type ) {
			return new self( $post );
		}
		return false;
	}

	/**
	 * Get a profile object based on its term id.
	 *
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_term_id(): Profile|false {
		return false;
	}

	/**
	 * Get a profile object based on its post slug.
	 *
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_slug(): Profile|false {
		return false;
	}

	/**
	 * Get a profile object based on its user id.
	 *
	 * @param int $user_id ID for the profile's user.
	 * @return Profile|false Profile on success, false on failure.
	 */
	public static function get_by_user_id( int $user_id ): Profile|false {
		if ( ! ( $user_id > 0 ) ) {
			return false;
		}

		$profile_id = get_user_meta( $user_id, self::user_meta_key(), true );

		if ( ! is_numeric( $profile_id ) || $profile_id <= 0 ) {
			// Try to fetch by legacy, unprefixed meta key.
			$profile_id = get_user_meta( $user_id, 'profile_id', true );
		}

		if ( ! is_numeric( $profile_id ) || $profile_id <= 0 ) {
			return false;
		}

		$profile = self::get_by_post( $profile_id );

		return $profile instanceof Profile && $profile->get_linked_user_id() === $user_id
			? $profile
			: false;
	}

	/**
	 * Instantiate a new Profile object
	 *
	 * Profiles are always fetched by static fetchers.
	 *
	 * @throws \InvalidArgumentException If the post ID is invalid.
	 *
	 * @param int|WP_Post $post Post ID or object of a profile.
	 */
	private function __construct( int|WP_Post $post ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post ) {
			throw new \InvalidArgumentException( 'Invalid post ID' );
		}

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

		$post = $this->get_post();

		return match ( $attribute ) {
			// Uses the profile link.
			'link'          => get_permalink( $post ),
			// These fields are actually on the Post object.
			'display_name'  => $post->post_title,
			'user_nicename' => $post->post_name,
			'description'   => $post->post_content,
			'post_id'       => $post->ID,
			'term_id'       => $this->get_term_id(),
			default         => get_post_meta( $this->post->ID, $attribute, true ),
		};
	}

	/**
	 * Get the term ID for the profile.
	 *
	 * @return int
	 */
	private function get_term_id(): int {
		if ( ! isset( $this->_term_id ) ) {
			$this->_term_id = absint( get_post_meta( $this->post->ID, 'byline_id', true ) );
		}

		return absint( $this->_term_id );
	}

	/**
	 * Get the post object for the profile.
	 *
	 * @return WP_Post|null
	 */
	public function get_post(): ?WP_Post {
		return $this->post ?? null;
	}

	/**
	 * Update the profile's link to a user, and reciprocally link the user to the profile.
	 *
	 * @param int $new_user_id User ID to link. Set to 0 to unlink.
	 */
	public function update_user_link( $new_user_id ): void {
		$new_user_id   = (int) $new_user_id;
		$user_meta_key = self::user_meta_key();

		// First, check to see if this profile is linked, for reciprocal updates.
		$old_user_id = (int) get_post_meta( $this->post_id, 'user_id', true );

		if ( $old_user_id && $old_user_id !== $new_user_id ) {
			delete_post_meta( $this->post_id, 'user_id' );
			delete_user_meta( $old_user_id, $user_meta_key, $this->post_id );
		}

		// Save the post meta.
		if ( $new_user_id > 0 ) {
			update_post_meta( $this->post_id, 'user_id', $new_user_id );
			update_user_meta( $new_user_id, $user_meta_key, $this->post_id );
		}
	}

	/**
	 * Get the linked user ID for the current profile.
	 *
	 * @return int User ID or 0 if this profile is not linked.
	 */
	public function get_linked_user_id(): int {
		$user_id = get_post_meta( $this->get_post()->ID, 'user_id', true );

		return is_numeric( $user_id ) && $user_id > 0 ? (int) $user_id : 0;
	}

	/**
	 * Get the blog-specific user meta key that stores a user's linked profile ID.
	 *
	 * @return string Meta key.
	 */
	private static function user_meta_key(): string {
		global $wpdb;

		return 'byline_manager_' . $wpdb->get_blog_prefix() . 'profile_id';
	}
}
