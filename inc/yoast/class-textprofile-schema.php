<?php
/**
 * TextProfile_Schema class file
 *
 * @package Byline_Manager
 */

namespace Byline_Manager\Yoast;

use Byline_Manager\Models\TextProfile;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;
use Yoast\WP\SEO\Generators\Schema\Author;
use Yoast\WP\SEO\Surfaces\Helpers_Surface;

/**
 * Adds a Byline Manager text profile to the Yoast SEO schema graph.
 *
 * Extend Author for its `is_needed()` logic.
 */
final class TextProfile_Schema extends Author {
	/**
	 * Constructor.
	 *
	 * @param TextProfile       $profile TextProfile instance.
	 * @param Helpers_Surface   $helpers Helpers surface.
	 * @param Meta_Tags_Context $context Meta tags context.
	 */
	public function __construct(
		private readonly TextProfile $profile,
		public $helpers,
		public $context,
	) {}

	/**
	 * Generates the schema piece.
	 *
	 * @return mixed
	 */
	public function generate() {
		return [
			'@type' => 'Person',
			'@id'   => $this->context->site_url . Schema_IDs::PERSON_HASH . wp_hash( $this->profile->display_name ),
			'name'  => $this->helpers->schema->html->smart_strip_tags( $this->profile->display_name ),
		];
	}
}
