<?php
/**
 * Class Test_Bylines_Template_Tags
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;

/**
 * Test functionality related to the Profile object.
 */
class Test_Bylines_Template_Tags extends \WP_UnitTestCase {

	/**
	 * Getting bylines generically
	 */
	public function test_get_byline_entries_for_post() {
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );
		$post_id = $this->factory->post->create();
		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
			],
		];

		Utils::set_post_byline( $post_id, $byline_meta );
		$byline = Utils::get_byline_entries_for_post( $post_id );
		$this->assertCount( 2, $byline );
		$this->assertEquals( [ $b1->post_id, $b2->post_id ], wp_list_pluck( $byline, 'post_id' ) );

		// Ensure the order persists.
		$byline_meta['byline_entries'] = array_reverse( $byline_meta['byline_entries'] );
		Utils::set_post_byline( $post_id, $byline_meta );
		$byline = Utils::get_byline_entries_for_post( $post_id );
		$this->assertCount( 2, $byline );
		$this->assertEquals( [ $b2->post_id, $b1->post_id ], wp_list_pluck( $byline, 'post_id' ) );
	}

	/**
	 * Ensure get_byline_entries_for_post() returns a user object when no bylines are assigned
	 */
	public function test_get_byline_entries_for_post_returns_wp_user() {
		$this->markTestSkipped( 'TODO: how to handle posts without byline' );

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( [
			'post_author' => $user_id,
		] );
		$byline = Utils::get_byline_entries_for_post( $post_id );
		$this->assertCount( 1, $byline );
		$this->assertEquals( [ $user_id ], wp_list_pluck( $byline, 'ID' ) );
		// Adding a byline means the user id should no longer be returned.
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		Utils::set_post_byline( $post_id, [ 'byline_ids' => wp_list_pluck( [ $b1 ], 'term_id' ) ] );
		$byline = Utils::get_byline_entries_for_post( $post_id );
		$this->assertCount( 1, $byline );
		$this->assertEquals( [ 'b1' ], wp_list_pluck( $byline, 'post_name' ) );
	}

	/**
	 * Render one byline, without the link to its post
	 */
	public function test_template_tag_the_byline_one_byline() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( 'Byline 1' );
		the_byline();
	}

	/**
	 * Render two bylines, without the link to its post
	 */
	public function test_template_tag_the_byline_two_byline() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );

		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( 'Byline 2 and Byline 1' );
		the_byline();
	}

	/**
	 * Render three bylines, without the link to its post
	 */
	public function test_template_tag_the_byline_three_byline() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );
		$b3 = Profile::create( [
			'post_name'  => 'b3',
			'post_title' => 'Byline 3',
		] );

		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b3->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];

		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( 'Byline 2, Byline 3, and Byline 1' );
		the_byline();
	}

	/**
	 * Render four bylines, without the link to its post
	 */
	public function test_template_tag_the_byline_four_byline() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );
		$b3 = Profile::create( [
			'post_name'  => 'b3',
			'post_title' => 'Byline 3',
		] );
		$b4 = Profile::create( [
			'post_name'  => 'b4',
			'post_title' => 'Byline 4',
		] );

		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b4->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b3->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];

		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( 'Byline 2, Byline 4, Byline 3, and Byline 1' );
		the_byline();
	}

	/**
	 * Render two bylines, with the link to its post
	 */
	public function test_template_tag_the_byline_posts_links_two_byline() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );

		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];

		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( '<a href="' . $b2->link . '" title="Posts by Byline 2" class="author url fn" rel="author">Byline 2</a> and <a href="' . $b1->link . '" title="Posts by Byline 1" class="author url fn" rel="author">Byline 1</a>' );
		the_byline_posts_links();
	}

	/**
	 * Render one user, with the link to its post
	 */
	public function test_template_tag_the_byline_posts_links_one_user() {
		$this->markTestSkipped( 'TODO: how to handle posts without byline' );

		global $post;
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			[
				'post_author' => $user_id,
			]
		);
		$post = get_post( $post_id );
		$user = get_user_by( 'id', $user_id );
		$this->expectOutputString( '<a href="' . get_author_posts_url( $user_id ) . '" title="Posts by ' . $user->display_name . '" class="author url fn" rel="author">' . $user->display_name . '</a>' );
		the_byline_posts_links();
	}

	/**
	 * Render two bylines, one with a custom URL and the other without
	 */
	public function test_template_tag_the_byline_links_two_bylines() {
		global $post;
		$b1 = Profile::create( [
			'post_name'  => 'b1',
			'post_title' => 'Byline 1',
		] );
		$b2 = Profile::create( [
			'post_name'  => 'b2',
			'post_title' => 'Byline 2',
		] );
		update_post_meta( $b2->post_id, 'user_url', 'https://apple.com' );

		$byline_meta = [
			'byline_entries' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b2->term_id,
					],
				],
				[
					'type' => 'byline_id',
					'atts' => [
						'byline_id' => $b1->term_id,
					],
				],
			],
		];

		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_byline( $post_id, $byline_meta );
		$this->expectOutputString( '<a href="https://apple.com" title="Visit Byline 2&#8217;s website" rel="external">Byline 2</a> and Byline 1' );
		the_byline_links();
	}

	/**
	 * Render byline override.
	 */
	public function test_get_override_for_post() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => 'Test Override 1',
			]
		);
		$this->assertSame( 'Test Override 1', get_echo( '\Byline_Manager\the_byline' ) );
	}

	/**
	 * Render byline override that has an HTML link without the link.
	 */
	public function test_get_override_with_link_for_post() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => '<a href="https://website.test/">Test Override 1</a>',
			]
		);
		$this->assertSame( 'Test Override 1', get_echo( '\Byline_Manager\the_byline' ) );
	}

	/**
	 * Render byline override via `the_byline_posts_links()`.
	 */
	public function test_template_tag_the_byline_posts_links_with_override() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => 'Test Override 2',
			]
		);
		$this->assertSame( 'Test Override 2', get_echo( '\Byline_Manager\the_byline_posts_links' ) );
	}

	/**
	 * Render byline override with a link via `the_byline_posts_links()`.
	 */
	public function test_template_tag_the_byline_posts_links_with_override_with_link() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => '<a href="https://website.test/">Test Override 2</a>',
			]
		);
		$this->assertSame( '<a href="https://website.test/">Test Override 2</a>', get_echo( '\Byline_Manager\the_byline_posts_links' ) );
	}

	/**
	 * Render byline override via `the_byline_links()`.
	 */
	public function test_template_tag_the_byline_links_with_override() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => 'Test Override 3',
			]
		);
		$this->assertSame( 'Test Override 3', get_echo( '\Byline_Manager\the_byline_links' ) );
	}

	/**
	 * Render byline override with a link via `the_byline_links()`.
	 */
	public function test_template_tag_the_byline_links_with_override_with_link() {
		$this->markTestSkipped( 'TODO: Replace override tests, as the old format is no longer relevant.' );
		global $post;
		$post = $this->factory->post->create_and_get();
		Utils::set_post_byline(
			$post->ID,
			[
				'source' => 'override',
				'override' => '<a href="https://website.test/">Test Override 3</a>',
			]
		);
		$this->assertSame( '<a href="https://website.test/">Test Override 3</a>', get_echo( '\Byline_Manager\the_byline_links' ) );
	}
}
