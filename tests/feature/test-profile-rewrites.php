<?php
/**
 * Profile Rewrite Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Mantle\Testkit\Test_Case;

/**
 * Test the rewrite rules of the profile post type.
 */
class Test_Profile_Rewrites extends Test_Case {

	/**
	 * Trackback, attachment, and comment rules are removed, also for a slug that holds a slash.
	 */
	public function test_unset_rewrites_removes_extra_rules_for_a_slug_with_a_slash() {
		$post_type = get_post_type_object( PROFILE_POST_TYPE );
		$original  = $post_type->rewrite;

		$post_type->rewrite['slug'] = 'about/team';

		try {
			$result = unset_rewrites(
				[
					'about/team/([^/]+)/trackback/?$'                 => 'trackback',
					'about/team/([^/]+)/attachment/([^/]+)/?$'        => 'attachment',
					'about/team/([^/]+)/comment-page-([0-9]{1,})/?$'  => 'comment',
					'about/team/([^/]+)/?$'                           => 'single',
					'other/([^/]+)/trackback/?$'                      => 'other',
				]
			);
		} finally {
			$post_type->rewrite = $original;
		}

		$this->assertSame(
			[
				'about/team/([^/]+)/?$'      => 'single',
				'other/([^/]+)/trackback/?$' => 'other',
			],
			$result
		);
	}
}
