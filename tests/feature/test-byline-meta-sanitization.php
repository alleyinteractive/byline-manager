<?php
/**
 * Byline Meta Sanitization Tests
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Mantle\Testkit\Test_Case;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Test that the byline meta is sanitized whenever it is saved.
 */
class Test_Byline_Meta_Sanitization extends Test_Case {

	/**
	 * Build byline meta holding a single text entry.
	 *
	 * @param string $text Text entry.
	 * @return array
	 */
	private function text_meta( string $text ): array {
		return [
			'profiles' => [
				[
					'type' => 'text',
					'atts' => [
						'text' => $text,
					],
				],
			],
		];
	}

	/**
	 * Script tags in a text entry are removed when the meta is saved.
	 */
	public function test_text_entry_script_is_removed_on_save() {
		$post_id = static::factory()->post->create();

		update_post_meta( $post_id, 'byline', $this->text_meta( 'Wire <script>alert(1)</script>Services' ) );

		$text = get_post_meta( $post_id, 'byline', true )['profiles'][0]['atts']['text'];

		$this->assertStringNotContainsString( '<script', $text );
		$this->assertStringContainsString( 'Wire', $text );
	}

	/**
	 * Markup that is allowed in post content is kept in a text entry.
	 */
	public function test_text_entry_safe_markup_is_kept_on_save() {
		$post_id = static::factory()->post->create();

		update_post_meta( $post_id, 'byline', $this->text_meta( '<em>Staff</em> Report' ) );

		$this->assertSame(
			'<em>Staff</em> Report',
			get_post_meta( $post_id, 'byline', true )['profiles'][0]['atts']['text']
		);
	}

	/**
	 * Profile entries are saved unchanged.
	 */
	public function test_profile_entry_is_not_changed_on_save() {
		$post_id = static::factory()->post->create();
		$meta    = [
			'profiles' => [
				[
					'type' => 'byline_id',
					'atts' => [
						'term_id' => 12,
						'post_id' => 34,
					],
				],
			],
		];

		update_post_meta( $post_id, 'byline', $meta );

		$this->assertSame( $meta, get_post_meta( $post_id, 'byline', true ) );
	}

	/**
	 * A text entry sent through the REST API, as the block editor does, is sanitized.
	 */
	public function test_rest_save_sanitizes_text_entry() {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$post_id = static::factory()->post->create();

		$request = new WP_REST_Request( WP_REST_Server::EDITABLE, "/wp/v2/posts/{$post_id}" );
		$request->set_body_params(
			[
				'meta' => [
					'byline' => $this->text_meta( 'Wire <script>alert(1)</script>Services' ),
				],
			]
		);

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$text = get_post_meta( $post_id, 'byline', true )['profiles'][0]['atts']['text'];

		$this->assertStringNotContainsString( '<script', $text );
	}
}
