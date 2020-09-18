<?php
/**
 * Representation of a text-only profile.
 *
 * @package Byline_Manager
 * @since 0.2.0
 */

namespace Byline_Manager\Models;

/**
 * Representation of an individual text-only profile.
 */
class TextProfile {
	/**
	 * An ID used by the Javascript to target the sortable item.
	 * Uses a millisecond timestamp prepended with 'text-'.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Not currently implemented, but useful to have defined.
	 *
	 * @var string
	 */
	public $display_name;

	/**
	 * Not currently implemented, but useful to have defined.
	 *
	 * @var string
	 */
	public $link = '';

	/**
	 * Instantiate a new Text Profile object
	 *
	 * Text Profiles are always fetched by static fetchers.
	 *
	 * @param array $atts Attributes for a text profile.
	 */
	private function __construct( array $atts ) {
		$this->atts         = $atts;
		$this->display_name = $atts['text'] ?? false;
		$this->id           = $this->generate_id();
	}

	/**
	 * Create a text profile object based on its attributes.
	 *
	 * @param array $atts Attributes for a text profile.
	 * @return TextProfile|false
	 */
	public static function create( $atts ) {
		// Require at least a text attribute.
		if ( ! empty( $atts['text'] ) ) {
			return new TextProfile( $atts );
		}
		return false;
	}

	/**
	 * Return an arbitrary unique ID based on a static counter.
	 * The front end needs a unique ID to work with, but it doesn't
	 * need to be preserved between page loads.
	 *
	 * @return string
	 */
	private function generate_id() {
		static $counter = 1;
		return 'text-' . ( $counter++ );
	}
}
