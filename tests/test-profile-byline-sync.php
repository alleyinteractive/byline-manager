<?php
/**
 * Class SampleTest
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Sample test case.
 */
class Profile_Byline_Sync_Test extends \WP_UnitTestCase {
	protected $profile_id;

	public function setUp() {
		parent::setUp();
		$this->profile_id = self::factory()->post->create( [ 'post_type' => PROFILE_POST_TYPE ] );
	}

	public function tearDown() {
		$this->reset_post_types();
		parent::tearDown();
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
		register_post_type( $post_type_no_author, [
			'public' => true,
			'supports' => [ 'title', 'editor' ],
		] );

		$post_type_with_author = 'test-with-author';
		register_post_type( $post_type_with_author, [
			'public' => true,
			'supports' => [ 'title', 'editor', 'author' ],
		] );

		unregister_taxonomy( BYLINE_TAXONOMY );
		register_byline();

		$this->assertFalse( is_object_in_taxonomy( $post_type_no_author, BYLINE_TAXONOMY ) );
		$this->assertTrue( is_object_in_taxonomy( $post_type_with_author, BYLINE_TAXONOMY ) );
	}
}
