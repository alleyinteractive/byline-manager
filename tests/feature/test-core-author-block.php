<?php
/**
 * Core Author Block Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Mantle\Testkit\Test_Case;
use WP_Block;

/**
 * Test the core post author block filter.
 */
class Test_Core_Author_Block extends Test_Case {

	/**
	 * Post author block markup.
	 *
	 * @var string
	 */
	private const BLOCK = '<!-- wp:post-author {"showAvatar":false} /-->';

	/**
	 * WordPress user who authors the posts under test.
	 *
	 * @var int
	 */
	private int $user_id;

	protected function setUp(): void {
		parent::setUp();

		$this->user_id = static::factory()->user->create( [ 'display_name' => 'WP Author' ] );

		// Hooks added during a test are restored afterwards, so add the filter explicitly.
		add_filter(
			'render_block_core/post-author',
			[ Core_Author_Block::get_instance(), 'append_and_filter_post_author_blocks' ],
			10,
			3
		);
	}

	/**
	 * Create a profile.
	 *
	 * @param string $title Profile title.
	 * @return Profile
	 */
	private function create_profile( string $title ): Profile {
		$profile = Profile::create(
			[
				'post_name'  => sanitize_title( $title ),
				'post_title' => $title,
			]
		);

		$this->assertInstanceOf( Profile::class, $profile );

		return $profile;
	}

	/**
	 * Create a post authored by the test user with a byline.
	 *
	 * @param array[] $entries Byline entries.
	 * @param string  $content Post content.
	 * @return int Post ID.
	 */
	private function create_post( array $entries, string $content = self::BLOCK ): int {
		$post_id = static::factory()->post->create(
			[
				'post_author'  => $this->user_id,
				'post_content' => $content,
			]
		);

		Utils::set_post_byline( $post_id, [ 'byline_entries' => $entries ] );

		return $post_id;
	}

	/**
	 * Build a profile byline entry.
	 *
	 * @param Profile $profile Profile.
	 * @return array
	 */
	private function profile_entry( Profile $profile ): array {
		return [
			'type' => 'byline_id',
			'atts' => [
				'byline_id' => $profile->term_id,
			],
		];
	}

	/**
	 * Build a text byline entry.
	 *
	 * @param string $text Text.
	 * @return array
	 */
	private function text_entry( string $text ): array {
		return [
			'type' => 'text',
			'atts' => [
				'text' => $text,
			],
		];
	}

	/**
	 * Render the content of a post as the front end does.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function render_post( int $post_id ): string {
		global $post;

		$post = get_post( $post_id );
		setup_postdata( $post );

		return do_blocks( $post->post_content );
	}

	/**
	 * Every post author block on a page is filtered, not only the first.
	 */
	public function test_every_post_author_block_is_filtered() {
		$b1 = $this->create_profile( 'Byline 1' );
		$b2 = $this->create_profile( 'Byline 2' );

		$post_id = $this->create_post(
			[ $this->profile_entry( $b1 ), $this->profile_entry( $b2 ) ],
			self::BLOCK . "\n\n<!-- wp:paragraph --><p>Body</p><!-- /wp:paragraph -->\n\n" . self::BLOCK
		);

		$output = $this->render_post( $post_id );

		// Two blocks, each duplicated once per byline entry.
		$this->assertSame( 2, substr_count( $output, '>Byline 1<' ) );
		$this->assertSame( 2, substr_count( $output, '>Byline 2<' ) );
	}

	/**
	 * A block uses the byline of the post in its context, not the post being viewed.
	 */
	public function test_block_uses_the_byline_of_the_post_in_its_context() {
		global $post;

		$b1 = $this->create_profile( 'Byline 1' );
		$b2 = $this->create_profile( 'Byline 2' );

		$first  = $this->create_post( [ $this->profile_entry( $b1 ) ] );
		$second = $this->create_post( [ $this->profile_entry( $b2 ) ] );
		$page   = static::factory()->post->create( [ 'post_type' => 'page' ] );

		// The page being viewed has no byline, like a page holding a query loop.
		$post = get_post( $page );
		setup_postdata( $post );

		$parsed = parse_blocks( self::BLOCK )[0];

		$first_output  = ( new WP_Block( $parsed, [ 'postId' => $first ] ) )->render();
		$second_output = ( new WP_Block( $parsed, [ 'postId' => $second ] ) )->render();

		$this->assertStringContainsString( '>Byline 1<', $first_output );
		$this->assertStringNotContainsString( 'Byline 2', $first_output );
		$this->assertStringContainsString( '>Byline 2<', $second_output );
		$this->assertStringNotContainsString( 'Byline 1', $second_output );
	}

	/**
	 * A post without a byline does not render the block.
	 */
	public function test_post_without_byline_renders_no_block() {
		$post_id = $this->create_post( [] );

		$this->assertSame( '', trim( $this->render_post( $post_id ) ) );
	}

	/**
	 * A text byline with an ampersand keeps its name.
	 */
	public function test_text_byline_with_ampersand_keeps_name() {
		$post_id = $this->create_post( [ $this->text_entry( 'Smith & Jones' ) ] );

		$this->assertStringContainsString( '>Smith &amp; Jones<', $this->render_post( $post_id ) );
	}

	/**
	 * A text byline that is already entity-encoded is not encoded twice.
	 */
	public function test_text_byline_with_encoded_ampersand_is_not_double_encoded() {
		$post_id = $this->create_post( [ $this->text_entry( 'Barnes &amp; Noble' ) ] );

		$this->assertStringContainsString( '>Barnes &amp; Noble<', $this->render_post( $post_id ) );
	}

	/**
	 * Markup in a text byline is removed from the name.
	 */
	public function test_text_byline_name_has_markup_removed() {
		$post_id = $this->create_post( [ $this->text_entry( '<em>Staff</em> Report' ) ] );

		$output = $this->render_post( $post_id );

		$this->assertStringContainsString( '>Staff Report<', $output );
		$this->assertStringNotContainsString( '<em>', $output );
	}

	/**
	 * A profile name with an ampersand keeps its name.
	 */
	public function test_profile_with_ampersand_keeps_name() {
		$profile = $this->create_profile( 'Smith &amp; Jones' );
		$post_id = $this->create_post( [ $this->profile_entry( $profile ) ] );

		$output = $this->render_post( $post_id );

		$this->assertStringContainsString( 'Smith &amp; Jones', $output );
		$this->assertStringNotContainsString( 'Smith &amp;amp;', $output );
	}

	/**
	 * Block content that is already empty is returned as it is.
	 */
	public function test_empty_block_content_is_returned_as_is() {
		$profile = $this->create_profile( 'Byline 1' );
		$post_id = $this->create_post( [ $this->profile_entry( $profile ) ] );

		$parsed = parse_blocks( self::BLOCK )[0];
		$block  = new WP_Block( $parsed, [ 'postId' => $post_id ] );

		$this->assertSame(
			'',
			Core_Author_Block::get_instance()->append_and_filter_post_author_blocks( '', $parsed, $block )
		);
	}
}
