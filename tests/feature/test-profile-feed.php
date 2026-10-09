<?php
/**
 * Profile Feed Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\Test_Case;

/**
 * Test the feed of a profile.
 */
class Test_Profile_Feed extends Test_Case {
	use Refresh_Database;

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
	 * Create a post with a profile in its byline, or without a byline.
	 *
	 * @param Profile|null $profile Profile for the byline.
	 * @return int Post ID.
	 */
	private function create_post( ?Profile $profile = null ): int {
		$post_id = static::factory()->post->create();

		if ( $profile ) {
			Utils::set_post_byline(
				$post_id,
				[
					'byline_entries' => [
						[
							'type' => 'byline_id',
							'atts' => [
								'byline_id' => $profile->term_id,
							],
						],
					],
				]
			);
		}

		return $post_id;
	}

	/**
	 * Request the feed of a profile.
	 *
	 * @param string $slug Profile slug.
	 * @return int[] IDs of the posts in the feed.
	 */
	private function feed_post_ids( string $slug ): array {
		global $wp_query;

		$this->get( '/?feed=rss2&' . FEED_PROFILE_QUERY_VAR . '=' . $slug );

		return array_map( 'intval', wp_list_pluck( $wp_query->posts, 'ID' ) );
	}

	/**
	 * The feed of a profile lists only the posts it is a byline of.
	 */
	public function test_feed_lists_only_the_posts_of_the_profile() {
		$one = $this->create_profile( 'one' );
		$two = $this->create_profile( 'two' );

		$post_one = $this->create_post( $one );
		$this->create_post( $two );
		$this->create_post();

		$this->assertSame( [ $post_one ], $this->feed_post_ids( 'one' ) );
	}

	/**
	 * A profile that does not exist has no feed.
	 */
	public function test_feed_of_unknown_profile_is_not_found() {
		$this->create_post( $this->create_profile( 'one' ) );

		$this->assertSame( [], $this->feed_post_ids( 'does-not-exist' ) );
		$this->assertTrue( is_404() );
	}

	/**
	 * A profile without a byline term has an empty feed, not the feed of every post.
	 */
	public function test_feed_of_profile_without_byline_term_is_empty() {
		$profile = $this->create_profile( 'one' );
		update_post_meta( $profile->post_id, 'byline_id', 'error' );

		$this->create_post();

		$this->assertSame( [], $this->feed_post_ids( 'one' ) );
		$this->assertFalse( is_404() );
	}
}
