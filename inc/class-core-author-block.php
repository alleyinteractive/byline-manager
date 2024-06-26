<?php
/**
 * Core Author Block class file
 *
 * This class is to filter the core post author block so that it displays info from the Byline Manager.
 *
 * @package Byline_Manager
 */

declare( strict_types = 1 );

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
 * Conditions
 */
class Core_Author_Block {
	/**
	 * Use the singleton.
	 *
	 * @use Singleton<static>
	 */
	use Singleton;

	/**
	 * Count of core author blocks.
	 *
	 * @var int
	 */
	public static int $core_author_blocks_count = 0;

	/**
	 * Array of bylines.
	 *
	 * @var array
	 */
	public static array $bylines = [];

	/**
	 * Initializes
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'render_block', [ $this, 'count_post_author_blocks' ], 10, 2 );
		add_filter( 'render_block_core/post-author', [ $this, 'append_and_filter_post_author_blocks' ], 12, 2 );
	}

	/**
	 * Counts the number of core/post-author blocks and bylines.
	 *
	 * @param string $block_content The block content.
	 * @param array<string, mixed> $block The full block, including name and attributes.
	 *
	 * @return string The block content.
	 */
	public function count_post_author_blocks( string $block_content, array $block ): string {
		global $post;

		$this::$bylines = get_post_meta( $post->ID, 'byline', true );

		if ('core/post-author' === $block['blockName']) {
			++ $this::$core_author_blocks_count;
		}

		return $block_content;
	}

	/**
	 * Filters the post author block data to use the bylines, also duplicates the post author block as many times
	 * as necessary to output all the bylines.
	 *
	 * @param string $block_content The block content.
	 * @param array<string, mixed> $block The full block, including name and attributes.
	 *
	 * @return string The block content.
	 */
	public function append_and_filter_post_author_blocks( string $block_content, array $block ): string {
		// Check that we have core author blocks and $bylines.
		if ( ! empty( $this::$core_author_blocks_count ) && ! empty( $this::$bylines['profiles'] ) ) {
			$bylines_count = count( $this::$bylines['profiles'] );
			// Calculate any difference between the bylines and the core author blocks.
			$blocks_difference = $bylines_count - $this::$core_author_blocks_count;

			$additional_blocks = '';
			if ( $blocks_difference > 0 ) {
				for ( $i = 0; $i < $bylines_count; $i ++ ) {
					$additional_blocks .= $this->filter_post_author_blocks( $block_content, $i );
				}

				// replace the original block with the original + new blocks
				$block_content = $additional_blocks;
			} else {
				$block_content = $this->filter_post_author_blocks( $block_content, 0 );
			}
		}
		return $block_content;
	}

	/**
	 * @param string $block_content The block content.
	 * @param int $i Used as an array key.
	 *
	 * @return string The modified core author block.
	 */
	public function filter_post_author_blocks( string $block_content, int $i ): string {
		// Setup some empty variables.
		$new_block        = '';
		$byline_type      = '';
		$profile_post     = '';
		$text_byline      = '';
		$new_author_block = '';
		$profiles         = $this::$bylines['profiles'];

		// Check if the byline uses a Profile Post ID.
		if ( 'byline_id' === $profiles[$i]['type'] && ! empty( $profiles[$i]['atts']['post_id'] ) ) {
			// Get the byline post.
			$profile_post = get_post( $profiles[$i]['atts']['post_id'] );
			$byline_type  = 'profile';
		}

		// Check if the byline uses a regular text.
		if ( 'text' === $profiles[$i]['type'] && ! empty( $profiles[$i]['atts']['text'] ) ) {
			// Get the byline text.
			$text_byline = $profiles[$i]['atts']['text'];
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
				return $block_content;
			}

			// Get the new block.
			$new_author_block = $this->replace_author_block_author( $block_content, $profile_post );
		}

		if ( $new_author_block && is_string( $new_author_block ) ) {
			$new_block = $new_author_block;
		}

		return $new_block;
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
}
