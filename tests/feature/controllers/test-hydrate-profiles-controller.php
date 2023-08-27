<?php
/**
 * Hydarte Profiles controller tests.
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use WP_REST_Request;
use WP_REST_Server;
use const Byline_Manager\REST_NAMESPACE;

/**
 * Test hydrate profiles controller.
 */
class Test_Hydrate_Profiles_Controller extends Test_Controller {

	public function set_up(): void {
		parent::set_up();

		$this->endpoint = '/' . REST_NAMESPACE . '/hydrateProfiles';
	}

	public function test_get_hydrated_profiles(): void {
		$user = self::factory()->user->create( [ 'role' => 'contributor' ] );

		wp_set_current_user( $user );

		$request = new WP_REST_Request( WP_REST_Server::CREATABLE, $this->endpoint );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_param( 'context', 'edit' );
		$request->set_body( wp_json_encode( [ 'profiles' => [] ] ) );

		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get_hydrated_profiles_without_permission(): void {
		$request = new WP_REST_Request( WP_REST_Server::CREATABLE, $this->endpoint );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_param( 'context', 'edit' );
		$request->set_body( wp_json_encode( [ 'profiles' => [] ] ) );

		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, rest_authorization_required_code() );
	}

	public function test_get_hydrated_profiles_with_subscriber(): void {
		$user = self::factory()->user->create();

		wp_set_current_user( $user );

		$request = new WP_REST_Request( WP_REST_Server::CREATABLE, $this->endpoint );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_param( 'context', 'edit' );
		$request->set_body( wp_json_encode( [ 'profiles' => [] ] ) );

		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, rest_authorization_required_code() );
	}
}
