<?php
/**
 * Test controller.
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Mantle\Testkit\Test_Case;
use WP_REST_Response;

/**
 * Test controller.
 */
class Test_Controller extends Test_Case {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '';

	protected function assertErrorResponse( $code, $response, $status = null ): void {

		if ( $response instanceof WP_REST_Response ) {
			$response = $response->as_error();
		}

		$this->assertWPError( $response );
		$this->assertSame( $code, $response->get_error_code() );

		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertSame( $status, $data['status'] );
		}
	}
}
