<?php
/**
 * Core Author Block class file
 *
 * This class filters the core post author block so that it displays info from the Byline Manager.
 * It also duplicates the core post author block as many times as necessary to display all the bylines.
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

/**
 * Sets up the Singleton trait use.
 *
 * @phpstan-use-trait-for-class Singleton
 */
use Byline_Manager\traits\Singleton;
use WP_Post;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Core_Author_Block class.
 */
class Core_Author_Block {
	/**
	 * Use the singleton.
	 *
	 * @use Singleton<static>
	 */
	use Singleton;

	/**
	 * Array of bylines.
	 *
	 * @var array
	 */
	public static array $bylines = [];

	/**
	 * Boolean that controls when to render the author blocks.
	 *
	 * @var bool
	 */
	public static bool $render = true;

	/**
	 * Initializes
	 *
	 * Gets required bylines post meta and setups a filter.
	 */
	public function init(): void {
		global $post;

		if ( ! empty( $post ) && property_exists( $post, 'ID' ) ) {
			// Get the byline meta and set it to our property.
			$byline         = get_post_meta( $post->ID, 'byline', true );
			$this::$bylines = ! empty( $byline ) ? $byline : [];
		}

		add_filter( 'render_block_core/post-author', [ $this, 'append_and_filter_post_author_blocks' ], 10, 2 );
	}

	/**
	 * Filters the post author block data to use the bylines, also duplicates the post author block as many times
	 * as necessary to output all the bylines.
	 *
	 * @param string               $block_content The block content.
	 * @param array<string, mixed> $block The full block, including name and attributes.
	 *
	 * @return string The block content.
	 */
	public function append_and_filter_post_author_blocks( $block_content, $block ): string {
		// Check that render is true and that we have bylines.
		if ( $this::$render && ! empty( $this::$bylines['profiles'] ) ) {
			// Count the bylines.
			$bylines_count = count( $this::$bylines['profiles'] );

			// A string variable that will be used for the final rendered content.
			$additional_blocks = '';

			// Loop through the total bylines count.
			for ( $i = 0; $i < $bylines_count; $i++ ) {
				// Filter the block and append it to our string variable, essentially duplicating it.
				$additional_blocks .= $this->filter_post_author_block( $block_content, $i );
			}

			// Replace the original block with the new blocks.
			$block_content = $additional_blocks;

			// Update our boolean to not render anymore.
			$this::$render = false;
		} else {
			// Essentially $this::$render is false, so return an empty string. Anything returned here will
			// print after our block(s), which we do not want.
			return '';
		}

		return $block_content;
	}

	/**
	 * Filters the author block data with byline info.
	 *
	 * @param string $block_content The block content.
	 * @param int    $index Used as an array key.
	 *
	 * @return string The modified core author block.
	 */
	public function filter_post_author_block( string $block_content, int $index ): string {
		// Setup some empty variables.
		$byline_type      = '';
		$profile_post     = '';
		$text_byline      = '';
		$new_author_block = '';
		$profiles         = $this::$bylines['profiles'];

		// Bail if the array item does not exist.
		if ( empty( $profiles[ $index ] ) ) {
			return $block_content;
		}

		// Check if the byline uses a Profile Post ID.
		if ( $this->validate_byline_post( $profiles[ $index ] ) ) {
			// Get the byline post.
			$profile_post = get_post( $profiles[ $index ]['atts']['post_id'] );
			$byline_type  = 'profile';
		}

		// Check if the byline uses a regular text.
		if ( $this->validate_byline_text( $profiles[ $index ] ) ) {
			// Get the byline text.
			$text_byline = $profiles[ $index ]['atts']['text'];
			$byline_type = 'text';
		}

		// Check if the profile is a text field.
		if ( ! empty( $text_byline ) && 'text' === $byline_type ) {
			// Get the new block.
			$new_author_block = $this->replace_author_block_name( $block_content, $text_byline );
		}

		// Check if the profile is a WP_Post.
		if ( 'profile' === $byline_type ) {
			if ( is_null( $profile_post ) || is_string( $profile_post ) || ! is_a( $profile_post, 'WP_Post' ) ) {
				// Return the original block if something is wrong with the profile post.
				return $block_content;
			}

			// Get the new block.
			$new_author_block = $this->replace_author_block_author( $block_content, $profile_post );
		}

		// Make sure the new block is valid.
		if ( $new_author_block && is_string( $new_author_block ) ) {
			$block_content = $new_author_block;
		}

		return $block_content;
	}

	/**
	 * Replaces the author name, avatar, bio and link in the author block.
	 *
	 * @param string  $html Author block HTML.
	 * @param WP_Post $profile_post Post object of the profile post type.
	 *
	 * @return bool|string Filtered author block.
	 */
	public function replace_author_block_author( string $html, WP_Post $profile_post ): bool|string {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );

		// Convert non-ASCII characters to HTML entities.
		$doc->loadHTML( mb_encode_numericentity( $html, [ 0x80, 0x10ffff, 0, 0xfffff ], 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$xpath = new DOMXPath( $doc );

		// Change the 'src' attribute of the 'img' tag.
		$image_query_result = $xpath->query( '//div[contains(@class, "wp-block-post-author__avatar")]/img' );
		$image_node         = ( $image_query_result instanceof DOMNodeList ) ? $image_query_result->item( 0 ) : null;
		if ( $image_node instanceof \DOMElement ) {
			// Get the new src image.
			$new_src = get_the_post_thumbnail_url( $profile_post->ID, 'thumbnail' );

			if ( $new_src ) {
				$image_node->setAttribute( 'src', $new_src );
				$image_node->setAttribute( 'srcset', $new_src . ' 2x' );
			}
		}

		// Change the text inside '.wp-block-post-author__name'.
		$name_query_result = $xpath->query( '//p[contains(@class, "wp-block-post-author__name")]' );
		$name_node         = ( $name_query_result instanceof DOMNodeList ) ? $name_query_result->item( 0 ) : null;
		if ( $name_node instanceof \DOMElement && property_exists( $profile_post, 'post_title' ) ) {
			// Check if the author name has an anchor.
			if ( $name_node->getElementsByTagName( 'a' )->length > 0 ) {
				$anchor_node = $name_node->getElementsByTagName( 'a' )->item( 0 );

				if ( $anchor_node instanceof \DOMElement ) {
					$anchor_node->nodeValue = $profile_post->post_title; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$permalink              = get_permalink( $profile_post->ID );

					if ( $permalink ) {
						$anchor_node->setAttribute( 'href', $permalink );
					}
				}
			} else {
				// Replace the author name.
				$name_node->nodeValue = $profile_post->post_title; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
		}

		// Change the text inside '.wp-block-post-author__bio'.
		$bio_query_result = $xpath->query( '//p[contains(@class, "wp-block-post-author__bio")]' );
		$bio_node         = ( $bio_query_result instanceof DOMNodeList ) ? $bio_query_result->item( 0 ) : null;
		if ( $bio_node && property_exists( $profile_post, 'post_content' ) ) {
			// Replace the author bio.
			$content             = apply_filters( 'the_content', $profile_post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$content             = str_replace( [ '<p>', '</p>' ], '', $content );
			$bio_node->nodeValue = $content; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $doc->saveHTML();
	}

	/**
	 * Replaces the author name in the author block.
	 *
	 * @param string $html Author block HTML.
	 * @param string $name The name of the author.
	 *
	 * @return bool|string
	 */
	public function replace_author_block_name( string $html, string $name ): bool|string {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );

		// Convert non-ASCII characters to HTML entities.
		$doc->loadHTML( mb_encode_numericentity( $html, [ 0x80, 0x10ffff, 0, 0xfffff ], 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$xpath = new DOMXPath( $doc );

		$name_query_result = $xpath->query( '//p[contains(@class, "wp-block-post-author__name")]' );
		$name_node         = ( $name_query_result instanceof DOMNodeList ) ? $name_query_result->item( 0 ) : null;
		if ( $name_node instanceof \DOMElement ) {
			// Replace the author name.
			$name_node->nodeValue = $name; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
		return $doc->saveHTML();
	}

	/**
	 * Validates the byline text profile.
	 *
	 * @param array $profile The profile to be validated.
	 *
	 * @return bool Returns true if the profile is valid, false otherwise.
	 */
	private function validate_byline_text( array $profile ): bool {
		return 'text' === $profile['type'] && ! empty( $profile['atts']['text'] );
	}

	/**
	 * Validates the profile for the byline post type.
	 *
	 * @param array $profile The profile data.
	 *
	 * @return bool Returns true if the profile is valid for the byline post type. Otherwise, returns false.
	 */
	private function validate_byline_post( array $profile ): bool {
		return 'byline_id' === $profile['type'] && ! empty( $profile['atts']['post_id'] );
	}
}
