<?php
/**
 * Profile User Link Save Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;

/**
 * Test saving the link between a profile and a user account from the profile editor.
 */
class Test_Profile_User_Link_Save extends Test_Case {
	use Refresh_Database;

	protected function tearDown(): void {
		$_POST = [];

		parent::tearDown();
	}

	/**
	 * Create a profile.
	 *
	 * @param string $slug Profile slug.
	 * @return Profile
	 */
	private function create_profile( string $slug ): Profile {
		$profile = Profile::create(
			[
				'post_name'  => $slug,
				'post_title' => ucfirst( $slug ),
			]
		);

		$this->assertInstanceOf( Profile::class, $profile );

		return $profile;
	}

	/**
	 * Save a profile as the editor does, with a user chosen in the user link box.
	 *
	 * @param Profile $profile Profile being saved.
	 * @param int     $user_id User ID sent by the form.
	 */
	private function save_profile_with_user( Profile $profile, int $user_id ): void {
		$_POST = [
			'profile_user_link_nonce' => wp_create_nonce( 'set_user_link' ),
			'profile_user_link'       => (string) $user_id,
		];

		set_profile_user_link( $profile->post_id, $profile->post );
	}

	/**
	 * A user without a profile is linked.
	 */
	public function test_unlinked_user_is_linked() {
		$user_id = static::factory()->user->create();
		$profile = $this->create_profile( 'one' );

		$this->save_profile_with_user( $profile, $user_id );

		$this->assertSame( $user_id, $profile->get_linked_user_id() );
		$this->assertSame( $profile->post_id, Profile::get_by_user_id( $user_id )->post_id );
	}

	/**
	 * A user who already belongs to another profile is not taken over.
	 */
	public function test_user_linked_to_another_profile_is_not_taken_over() {
		$user_id = static::factory()->user->create();
		$owner   = $this->create_profile( 'owner' );
		$other   = $this->create_profile( 'other' );

		$owner->update_user_link( $user_id );

		$this->save_profile_with_user( $other, $user_id );

		$this->assertSame( 0, $other->get_linked_user_id() );
		$this->assertSame( $user_id, $owner->get_linked_user_id() );
		$this->assertSame( $owner->post_id, Profile::get_by_user_id( $user_id )->post_id );
	}

	/**
	 * The current link is kept when a refused user is sent.
	 */
	public function test_refused_user_keeps_the_current_link() {
		$current = static::factory()->user->create();
		$taken   = static::factory()->user->create();
		$owner   = $this->create_profile( 'owner' );
		$other   = $this->create_profile( 'other' );

		$owner->update_user_link( $taken );
		$other->update_user_link( $current );

		$this->save_profile_with_user( $other, $taken );

		$this->assertSame( $current, $other->get_linked_user_id() );
	}

	/**
	 * A user that does not exist is not linked.
	 */
	public function test_missing_user_is_not_linked() {
		$profile = $this->create_profile( 'one' );

		$this->save_profile_with_user( $profile, 999999 );

		$this->assertSame( 0, $profile->get_linked_user_id() );
		$this->assertEmpty( get_user_meta( 999999 ) );
	}

	/**
	 * Saving a profile again with its own user keeps the link.
	 */
	public function test_saving_with_its_own_user_keeps_the_link() {
		$user_id = static::factory()->user->create();
		$profile = $this->create_profile( 'one' );

		$profile->update_user_link( $user_id );

		$this->save_profile_with_user( $profile, $user_id );

		$this->assertSame( $user_id, $profile->get_linked_user_id() );
	}

	/**
	 * Sending no user removes the link.
	 */
	public function test_sending_no_user_removes_the_link() {
		$user_id = static::factory()->user->create();
		$profile = $this->create_profile( 'one' );

		$profile->update_user_link( $user_id );

		$this->save_profile_with_user( $profile, 0 );

		$this->assertSame( 0, $profile->get_linked_user_id() );
		$this->assertFalse( Profile::get_by_user_id( $user_id ) );
	}
}
