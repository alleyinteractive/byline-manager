<?php
/**
 * Profile Lookup Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;

/**
 * Test finding a profile by its byline term and by its slug.
 */
class Test_Profile_Lookups extends Test_Case {
	use Refresh_Database;

	/**
	 * Create a profile.
	 *
	 * @param string $slug   Profile slug.
	 * @param string $status Post status.
	 * @return Profile
	 */
	private function create_profile( string $slug, string $status = 'publish' ): Profile {
		$profile = Profile::create(
			[
				'post_name'   => $slug,
				'post_title'  => ucfirst( $slug ),
				'post_status' => $status,
			]
		);

		$this->assertInstanceOf( Profile::class, $profile );

		return $profile;
	}

	/**
	 * A profile is found by its byline term.
	 */
	public function test_get_by_term_id_finds_the_profile() {
		$profile = $this->create_profile( 'one' );

		$found = Profile::get_by_term_id( $profile->term_id );

		$this->assertInstanceOf( Profile::class, $found );
		$this->assertSame( $profile->post_id, $found->post_id );
	}

	/**
	 * A term that is not a byline term, or no term, finds nothing.
	 */
	public function test_get_by_term_id_finds_nothing_for_other_terms() {
		$category = static::factory()->category->create();

		$this->assertFalse( Profile::get_by_term_id( $category ) );
		$this->assertFalse( Profile::get_by_term_id( 999999 ) );
		$this->assertFalse( Profile::get_by_term_id() );
	}

	/**
	 * A byline term whose name does not hold a post ID finds nothing.
	 */
	public function test_get_by_term_id_finds_nothing_for_a_term_without_a_post_id() {
		$term = wp_insert_term( 'noprofile', BYLINE_TAXONOMY );

		$this->assertFalse( Profile::get_by_term_id( $term['term_id'] ) );
	}

	/**
	 * A published profile is found by its slug.
	 */
	public function test_get_by_slug_finds_the_profile() {
		$profile = $this->create_profile( 'one' );

		$found = Profile::get_by_slug( 'one' );

		$this->assertInstanceOf( Profile::class, $found );
		$this->assertSame( $profile->post_id, $found->post_id );
	}

	/**
	 * An unknown slug, an empty slug, and the slug of a draft find nothing.
	 */
	public function test_get_by_slug_finds_nothing_for_unknown_or_unpublished_profiles() {
		$this->create_profile( 'draft', 'draft' );

		$this->assertFalse( Profile::get_by_slug( 'does-not-exist' ) );
		$this->assertFalse( Profile::get_by_slug( 'draft' ) );
		$this->assertFalse( Profile::get_by_slug() );
	}

	/**
	 * The slug of another post type finds nothing.
	 */
	public function test_get_by_slug_ignores_other_post_types() {
		static::factory()->post->create( [ 'post_name' => 'a-post' ] );

		$this->assertFalse( Profile::get_by_slug( 'a-post' ) );
	}
}
