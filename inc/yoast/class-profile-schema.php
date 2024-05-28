<?php
/**
 * Profile_Schema class file
 *
 * @package Byline_Manager
 */

namespace Byline_Manager\Yoast;

use Byline_Manager\Models\Profile;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;
use Yoast\WP\SEO\Generators\Schema\Author;
use Yoast\WP\SEO\Surfaces\Helpers_Surface;

/**
 * Adds a Byline Manager profile to the Yoast SEO schema graph.
 *
 * Extend the Author class for its `is_needed()` logic.
 */
final class Profile_Schema extends Author {
	/**
	 * Constructor.
	 *
	 * @param Profile           $profile Profile instance.
	 * @param Helpers_Surface   $helpers Helpers surface.
	 * @param Meta_Tags_Context $context Meta tags context.
	 */
	public function __construct(
		private readonly Profile $profile,
		public $helpers,
		public $context,
	) {}

	/**
	 * Generates the schema piece.
	 *
	 * @return mixed
	 */
	public function generate() {
		$post = $this->profile->get_post();

		$data = [
			'@type' => 'Person',
			'@id'   => $post->guid,
			'name'  => $this->helpers->schema->html->smart_strip_tags( $this->profile->display_name ),
			'url'   => $this->profile->link,
		];

		$excerpt = $this->helpers->schema->html->smart_strip_tags( get_the_excerpt( $post ) );

		if ( $excerpt ) {
			$data['description'] = $excerpt;
		}

		if ( has_post_thumbnail( $post ) ) {
			$data['image'] = $this->helpers->schema->image->generate_from_attachment_id(
				$this->context->site_url . Schema_IDs::PERSON_LOGO_HASH,
				get_post_thumbnail_id( $post ),
				''
			);
		}

		/**
		 * Filters the Yoast SEO schema graph data for the Byline Manager profile.
		 *
		 * @param array   $data    The schema data for the profile.
		 * @param Profile $profile The Byline Manager profile.
		 */
		return apply_filters( 'byline_manager_yoast_profile_schema', $data, $this->profile );
	}
}
