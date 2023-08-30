<?php
/**
 * Users controller tests.
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use WP_REST_Request;
use WP_REST_Server;
use const Byline_Manager\REST_NAMESPACE;

/**
 * Test users controller.
 */
class Test_Users_Controller extends Test_Controller {

	public function set_up() {
		parent::set_up();

		$this->endpoint = '/' . REST_NAMESPACE . '/users';
	}

	public function test_get_authors(): void {
		$user = self::factory()->user->create();

		wp_set_current_user( $user );

		$request = new WP_REST_Request( WP_REST_Server::READABLE, $this->endpoint );

		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get_authors_without_permission(): void {
		$request = new WP_REST_Request( WP_REST_Server::READABLE, $this->endpoint );

		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, rest_authorization_required_code() );
	}
}
