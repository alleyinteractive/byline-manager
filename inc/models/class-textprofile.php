<?php
/**
 * Representation of a text-only profile.
 *
 * @package Byline_Manager
 */

namespace Byline_Manager\Models;

class TextProfile {

	/**
	 * Not currently implemented, but useful to have defined.
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
		$this->atts = $atts;
		$this->display_name = strip_tags( $atts['text'] ) ?? false;
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
}
