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

	public function test_update_link() {
		$profile = Profile::get_by_post( $this->profile_id );
		$user1   = self::factory()->user->create( [ 'role' => 'author' ] );
		$user2   = self::factory()->user->create( [ 'role' => 'editor' ] );

		$this->assertSame( 0, $profile->get_linked_user_id() );

		// Link user1 and confirm that all data got set properly.
		$profile->update_user_link( $user1 );
		$this->assertSame( $user1, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, absint( get_user_meta( $user1, 'profile_id', true ) ) );

		// Link user2 and confirm that all data got unset and reset properly.
		$profile->update_user_link( $user2 );
		$this->assertSame( $user2, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, absint( get_user_meta( $user2, 'profile_id', true ) ) );

		// user1 should now have no profile_id meta.
		$this->assertSame( '', get_user_meta( $user1, 'profile_id', true ) );
	}

	public function test_unlink() {
		$profile = Profile::get_by_post( $this->profile_id );
		$user    = self::factory()->user->create( [ 'role' => 'author' ] );

		// Link user and confirm that all data got set properly.
		$profile->update_user_link( $user );
		$this->assertSame( $user, $profile->get_linked_user_id() );
		$this->assertSame( $this->profile_id, absint( get_user_meta( $user, 'profile_id', true ) ) );

		// Unlink user 2 and confirm that all data got unset properly.
		$profile->update_user_link( 0 );
		$this->assertSame( 0, $profile->get_linked_user_id() );
		$this->assertSame( '', get_user_meta( $user, 'profile_id', true ) );
	}
}
