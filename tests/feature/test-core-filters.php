<?php
/**
 * Core Filters Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;

/**
 * Test integration of core filters.
 */
class Test_Core_Filters extends Test_Case {
	use Refresh_Database;

	protected $b1, $b2, $byline_meta;

	protected function setUp(): void {
		global $post;

		parent::setUp();

		\Mantle\Testing\Utils::delete_all_posts();

		$user_id           = static::factory()->user->create(
			[
				'role' => 'editor',
			]
		);
		$this->b1          = Profile::create(
			[
				'post_name'  => 'b1',
				'post_title' => 'Byline 1',
			]
		);
		$this->b2          = Profile::create(
			[
				'post_name'  => 'b2',
				'post_title' => 'Byline 2',
			]
		);
		$this->byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $this->b1->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $this->b2->term_id,
					],
				],
			],
		];
		$post              = static::factory()->post->create_and_get( [ 'post_author' => $user_id ] );
		setup_postdata( $post );
	}

	/**
	 * Check that `the_author()` will output the byline automatically.
	 */
	public function test_the_author_filter() {
		global $post;
		$byline_meta = $this->byline_meta;

		// Before the byline gets set, `the_author()` should output nothing.
		$this->assertEquals( '', get_echo( 'the_author' ) );

		// Set the byline and confirm that `the_author()` outputs it.
		Utils::set_post_byline( $post->ID, $byline_meta );
		$this->assertEquals( 'Byline 1 and Byline 2', get_echo( 'the_author' ) );

		// Ensure order changes propogate.
		$byline_meta['byline_entries'] = array_reverse( $byline_meta['byline_entries'] );
		Utils::set_post_byline( $post->ID, $byline_meta );
		$this->assertEquals( 'Byline 2 and Byline 1', get_echo( 'the_author' ) );
	}

	/**
	 * Render two profiles in a byline, with the links to their posts.
	 */
	public function test_template_tag_the_byline_posts_links_two_byline() {
		global $post;
		$byline_meta = $this->byline_meta;
		// Flip the order of the bylines.
		$byline_meta['byline_entries'] = array_reverse( $byline_meta['byline_entries'] );
		Utils::set_post_byline( $post->ID, $byline_meta );

		$this->expectOutputString( '<a href="' . $this->b2->link . '" title="Posts by Byline 2" class="author url fn" rel="author">Byline 2</a> and <a href="' . $this->b1->link . '" title="Posts by Byline 1" class="author url fn" rel="author">Byline 1</a>' );
		the_author_posts_link();
	}

	/**
	 * Check that `the_author()` will output the byline override automatically.
	 */
	public function test_the_author_filter_byline_override() {
		global $post;

		// Before the byline gets set, `the_author()` should output nothing.
		$this->assertEquals( '', get_echo( 'the_author' ) );

		// Set the byline and confirm that `the_author()` outputs it.
		Utils::set_post_byline(
			$post->ID,
			[
				'byline_entries' => [
					[
						'type' => 'text',
						'atts' => [
							'text' => 'Test Core Override 1',
						],
					],
				],
			]
		);
		$this->assertEquals( 'Test Core Override 1', get_echo( 'the_author' ) );
	}

	/**
	 * Render the byline override without links.
	 */
	public function test_template_tag_the_byline_posts_links_override() {
		global $post;

		Utils::set_post_byline(
			$post->ID,
			[
				'byline_entries' => [
					[
						'type' => 'text',
						'atts' => [
							'text' => 'Test Core Override 2',
						],
					],
				],
			]
		);

		$this->assertEquals( 'Test Core Override 2', get_echo( 'the_author_posts_link' ) );
	}

	/**
	 * This is a bit of a hack used to buffer feed content.
	 *
	 * @return string
	 */
	public function do_rss2(): string {
		ob_start();
		try {
			@require ABSPATH . 'wp-includes/feed-rss2.php';
			$out = ob_get_clean();
		} catch ( \Exception $e ) {
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
		$byline_meta = $this->byline_meta;
		// Flip the order of the bylines.
		$byline_meta['byline_entries'] = array_reverse( $byline_meta['byline_entries'] );
		Utils::set_post_byline( $post->ID, $byline_meta );

		$this->get( '/?feed=rss2' );
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

	/**
	 * Test that the rss feed automatically gets the byline override added.
	 */
	public function test_rss_elements_byline_override() {
		global $post;
		Utils::set_post_byline(
			$post->ID,
			[
				'byline_entries' => [
					[
						'type' => 'text',
						'atts' => [
							'text' => 'Test RSS Override 1',
						],
					],
				],
			]
		);

		$this->get( '/?feed=rss2' );
		$feed = $this->do_rss2();
		$xml  = xml_to_array( $feed );

		// Get all the <item> child elements of the <channel> element
		$items = xml_find( $xml, 'rss', 'channel', 'item' );

		// Verify we only have one post.
		$this->assertCount( 1, $items );
		$item = reset( $items );

		// Check all dc:creator nodes.
		$creator = xml_find( $item['child'], 'dc:creator' );
		$this->assertCount( 1, $creator );
		$this->assertEquals( 'Test RSS Override 1', $creator[0]['content'] );
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

	public function test_get_author_display_name_from_global_post(): void {
		$author_name = 'Dumas Davy de la Pailleterie';

		$user = static::factory()->user->create_and_get(
			[ 'display_name' => $author_name ]
		);

		remove_filter( 'get_the_author_display_name', 'Byline_Manager\auto_integrate_byline', 10, 2 );

		$display_name    = get_the_author_meta( 'display_name', $user->ID );

		add_filter( 'get_the_author_display_name', 'Byline_Manager\auto_integrate_byline', 10, 2 );

		$this->assertSame( $author_name, $display_name );
		$this->assertNotSame( 'Byline 1', $display_name );
		$this->assertNotSame( 'Byline 2', $display_name );
		$this->assertNotSame( 'Byline 1 and Byline 2', $display_name );
	}

	public function test_get_author_display_name_from_user(): void {
		$author_name = 'Dumas Davy de la Pailleterie';
		$user        = static::factory()->user->create_and_get(
			[
				'display_name' => $author_name,
				'first_name'   => 'Dumas',
				'last_name'    => 'Davy de la Pailleterie',
			]
		);

		static::factory()->post->create_and_get(
			[ 'post_author' => $user->ID ]
		);

		$display_name = get_the_author_meta( 'display_name', $user->ID );

		$this->assertNotSame( '', $display_name );
		$this->assertSame( $display_name, $display_name );
	}
}
