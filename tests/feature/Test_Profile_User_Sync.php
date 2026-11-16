<?php
/**
 * Profile User Sync Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;

/**
 * Test profile -> user sync functionality.
 */
class Test_Profile_User_Sync extends Test_Case {
	use Refresh_Database;

	protected $profile_id;

	protected function setUp(): void {
		parent::setUp();
		$this->profile_id = self::factory()->post->create( [ 'post_type' => PROFILE_POST_TYPE ] );
	}

	public function test_update_link(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user1   = self::factory()->user->create( [ 'role' => 'author' ] );
		$user2   = self::factory()->user->create( [ 'role' => 'editor' ] );

		$this->assertSame( 0, $profile->get_linked_user_id() );

		// Link user1 and confirm that all data got set properly.
		$profile->update_user_link( $user1 );
		$this->assertSame( $user1, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user1 )->post_id );

		// Link user2 and confirm that all data got unset and reset properly.
		$profile->update_user_link( $user2 );
		$this->assertSame( $user2, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user2 )->post_id );

		// user1 should no longer be linked to a profile.
		$this->assertFalse( Profile::get_by_user_id( $user1 ) );
	}

	public function test_unlink(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		// Link user and confirm that all data got set properly.
		$profile->update_user_link( $user );
		$this->assertSame( $user, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );

		// Unlink user 2 and confirm that all data got unset properly.
		$profile->update_user_link( 0 );
		$this->assertEmpty( $profile->get_linked_user_id() );
		$this->assertFalse( Profile::get_by_user_id( $user ) );
	}

	public function test_profile_from_associated_user(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		// Link user and confirm that all data got set properly.
		$profile->update_user_link( $user );
		$this->assertSame( $user, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );

		// Delete profile post.
		wp_delete_post( $this->profile_id, true );

		// Confirm the associated user meta was deleted.
		$this->assertEmpty( $profile->get_linked_user_id() );
		$this->assertFalse( Profile::get_by_user_id( $user ) );
	}

	public function test_profile_from_deleted_user(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		// Link user and confirm that all data got set properly.
		$profile->update_user_link( $user );
		$this->assertSame( $user, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );

		// Delete user.
		require_once(ABSPATH . 'wp-admin/includes/user.php');
		wp_delete_user( $user );

		// Confirm the profile associated user meta was deleted.
		$this->assertEmpty( $profile->get_linked_user_id() );
	}

	public function test_get_by_user_id_with_legacy_meta(): void {
		$user = self::factory()->user->create( [ 'role' => 'author' ] );

		$this->create_unprefixed_reciprocal_meta( $this->profile_id, $user );

		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );

		// Reading is not supposed to write. Only update_user_link() populates the newer key.
		$this->assertEmpty( get_user_meta( $user, $this->prefixed_user_meta_key(), true ) );
	}

	public function test_get_by_user_id_ignores_legacy_meta_belonging_to_another_user(): void {
		$user1 = self::factory()->user->create( [ 'role' => 'author' ] );
		$user2 = self::factory()->user->create( [ 'role' => 'author' ] );

		$this->create_unprefixed_reciprocal_meta( $this->profile_id, $user1 );

		/*
		 * The legacy key is shared across a multisite network, so a value that does not describe
		 * a profile on this site containing the reciprocal meta has to be ignored.
		 */
		update_user_meta( $user2, 'profile_id', $this->profile_id );

		$this->assertFalse( Profile::get_by_user_id( $user2 ) );
	}

	public function test_update_link_populates_the_blog_prefixed_user_meta(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		$profile->update_user_link( $user );

		$this->assertSame( $this->profile_id, (int) get_user_meta( $user, $this->prefixed_user_meta_key(), true ) );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );
		$this->assertEmpty( get_user_meta( $user, 'profile_id', true ) );
	}

	public function test_update_link_migrates_a_legacy_link(): void {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		$this->create_unprefixed_reciprocal_meta( $this->profile_id, $user );

		// Re-linking the user the profile already names is what the migration command does.
		$profile->update_user_link( $user );

		$this->assertSame( $this->profile_id, (int) get_user_meta( $user, $this->prefixed_user_meta_key(), true ) );
		$this->assertSame( $this->profile_id, Profile::get_by_user_id( $user )->post_id );

		/*
		 * The legacy key is shared, so a single site must not delete it. Only the migration
		 * command clears it, once every site in the network has been visited.
		 */
		$this->assertSame( $this->profile_id, (int) get_user_meta( $user, 'profile_id', true ) );
	}

	/**
	 * Link a profile and a user the way the plugin did before the user meta key was prefixed.
	 *
	 * @param int $profile_id Profile post ID.
	 * @param int $user_id    User ID.
	 */
	private function create_unprefixed_reciprocal_meta( int $profile_id, int $user_id ): void {
		update_post_meta( $profile_id, 'user_id', $user_id );
		update_user_meta( $user_id, 'profile_id', $profile_id );
	}

	/**
	 * Get the blog-prefixed user meta key that stores a user's linked profile ID.
	 *
	 * @return string Meta key.
	 */
	private function prefixed_user_meta_key(): string {
		global $wpdb;

		return 'byline_manager_' . $wpdb->get_blog_prefix() . 'profile_id';
	}
}
