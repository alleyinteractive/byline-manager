<?php
/**
 * Core Filters Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;

/**
 * Test integration of core filters.
 */
class Test_Core_Filters extends \WP_UnitTestCase {
	protected $b1, $b2;

	public function setUp() {
		global $post;

		parent::setUp();

		$user_id = $this->factory->user->create( [
			'role' => 'editor',
		] );
		$this->b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$this->b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );
		$post = $this->factory->post->create_and_get( [ 'post_author' => $user_id ] );
		setup_postdata( $post );
	}

	/**
	 * Check that `the_author()` will output the byline automatically.
	 */
	public function test_the_author_filter() {
		global $post;

		// Before the byline gets set, `the_author()` should output nothing.
		$this->assertEquals( '', get_echo( 'the_author' ) );

		// Set the byline and confirm that `the_author()` outputs it.
		Utils::set_post_byline( $post->ID, wp_list_pluck( [ $this->b1, $this->b2 ], 'term_id' ) );
		$this->assertEquals( 'Byline 1 and Byline 2', get_echo( 'the_author' ) );

		// Ensure order changes propogate.
		Utils::set_post_byline( $post->ID, wp_list_pluck( [ $this->b2, $this->b1 ], 'term_id' ) );
		$this->assertEquals( 'Byline 2 and Byline 1', get_echo( 'the_author' ) );
	}

	/**
	 * Render two profiles in a byline, with the links to their posts.
	 */
	public function test_template_tag_the_byline_posts_links_two_byline() {
		global $post;

		Utils::set_post_byline( $post->ID, wp_list_pluck( [ $this->b2, $this->b1 ], 'term_id' ) );

		$this->expectOutputString( '<a href="' . $this->b2->link . '" title="Posts by Byline 2" class="author url fn" rel="author">Byline 2</a> and <a href="' . $this->b1->link . '" title="Posts by Byline 1" class="author url fn" rel="author">Byline 1</a>' );
		the_author_posts_link();
	}

	/**
	 * This is a bit of a hack used to buffer feed content.
	 */
	public function do_rss2() {
		ob_start();
		global $post;
		try {
			@require( ABSPATH . 'wp-includes/feed-rss2.php' );
			$out = ob_get_clean();
		} catch ( Exception $e ) {
			$out = ob_get_clean();
			throw($e);
		}
		return $out;
	}

	/**
	 * Test that the rss feed automatically gets the byline profiles added.
	 */
	public function test_rss_elements() {
		global $post;
		Utils::set_post_byline( $post->ID, wp_list_pluck( [ $this->b2, $this->b1 ], 'term_id' ) );

		$this->go_to( '/?feed=rss2' );
		$feed = $this->do_rss2();
		$xml  = xml_to_array( $feed );

		// Get all the <item> child elements of the <channel> element
		$items = xml_find( $xml, 'rss', 'channel', 'item' );

		// Verify we only have one post.
		$this->assertCount( 1, $items );
		$item = reset( $items );

		// Check all dc:creator nodes.
		$creator = xml_find( $item['child'], 'dc:creator' );
		$this->assertCount( 2, $creator );
		$this->assertEquals( 'Byline 2', $creator[0]['content'] );
		$this->assertEquals( 'Byline 1', $creator[1]['content'] );
	}

	public function test_author_link() {
		global $post;

		// Before a user is linked to a profile, it should have no posts url.
		$this->assertSame( '', get_author_posts_url( $post->post_author ) );

		// Link the profile and user, then recheck the url.
		$this->b1->update_user_link( $post->post_author );
		$this->assertSame(
			get_permalink( $this->b1->post_id ),
			get_author_posts_url( $post->post_author )
		);
	}
}
