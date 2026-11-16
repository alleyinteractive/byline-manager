<?php
/**
 * Profile Byline Sync Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;
/**
 * Test profile -> byline sync functionality.
 */
class Test_Profile_Byline_Sync extends Test_Case {
	use Refresh_Database;

	protected $profile_id;

	protected function setUp(): void {
		parent::setUp();
		$this->profile_id = self::factory()->post->create( [ 'post_type' => PROFILE_POST_TYPE ] );
	}

	public function test_profile_creates_byline() {
		$byline_id = absint( get_post_meta( $this->profile_id, 'byline_id', true ) );
		$this->assertGreaterThan( 0, $byline_id );

		$term = get_term( $byline_id, BYLINE_TAXONOMY );
		$this->assertInstanceOf( '\WP_Term', $term );
		$this->assertSame( $term->name, "profile-{$this->profile_id}" );
		$this->assertSame( $term->slug, "profile-{$this->profile_id}" );
	}

	public function test_profile_deletes_byline() {
		// Verify the byline term exists.
		$byline_id = absint( get_post_meta( $this->profile_id, 'byline_id', true ) );
		$this->assertInstanceOf( '\WP_Term', get_term( $byline_id, BYLINE_TAXONOMY ) );

		// Deleting the profile should delete the byline term.
		wp_delete_post( $this->profile_id, true );
		$this->assertNull( get_term( $byline_id, BYLINE_TAXONOMY ) );
	}

	public function test_byline_relationships() {
		$post_type_no_author = 'test-without-author';
		register_post_type(
			$post_type_no_author,
			[
				'public'   => true,
				'supports' => [ 'title', 'editor' ],
			]
		);

		unregister_taxonomy( BYLINE_TAXONOMY );
		register_byline();

		$this->assertFalse( is_object_in_taxonomy( $post_type_no_author, BYLINE_TAXONOMY ) );
	}
}
