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
	private static int $core_author_blocks_count = 0;

	/**
	 * Array of bylines.
	 *
	 * @var array
	 */
	private static array $bylines = [];


	/**
	 * Initializes
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'render_block', [ $this, 'count_post_author_blocks' ], 10, 2 );
		add_filter( 'render_block', [ $this, 'filter_post_author_block' ], 15, 2 );
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
		if ( 'core/post-author' === $block['blockName'] ) {
			++$this::$core_author_blocks_count;
		}

		global $post;

		$this::$bylines = get_post_meta( $post->ID, 'byline', true );

		return $block_content;
	}

	/**
	 * Callback function that checks if the rendered block is the `core/post-author`
	 * and then gets the byline meta of the current post to then pass along to `replace_author_block_author()`
	 *
	 * @param string               $block_content The block content.
	 * @param array<string, mixed> $block The full block, including name and attributes.
	 *
	 * @return string The block content.
	 */
	public function filter_post_author_block( string $block_content, array $block ): string {
		static $byline_count = 0;

		if ( 'core/post-author' === $block['blockName'] ) {
			$byline_type      = '';
			$profile_post     = '';
			$text_byline      = '';
			$new_author_block = '';

			if ( ! empty( $this::$bylines['profiles'] ) ) {
				foreach ( $this::$bylines['profiles'] as $profile ) {
					if ( 'byline_id' === $profile['type'] && ! empty( $profile['atts']['post_id'] ) ) {
						// The profile uses a Profile Post ID.
						$profile_post = get_post( $profile['atts']['post_id'] );
						$byline_type  = 'profile';
					}

					if ( 'text' === $profile['type'] && ! empty( $profile['atts']['text'] ) ) {
						// The profile uses text.
						$text_byline = $profile['atts']['text'];
						$byline_type = 'text';
					}
					++$byline_count;
				}
			}

			// Check that the profile is a text field.
			if ( ! empty( $text_byline ) && 'text' === $byline_type ) {
				// Get the new block.
				$new_author_block = $this->replace_author_block_name( $block_content, $text_byline );
			}

			// Check that the profile is a WP_Post.
			if ( 'profile' === $byline_type ) {
				if ( is_null( $profile_post ) || is_string( $profile_post ) || ! is_a( $profile_post, 'WP_Post' ) ) {
					return $block_content;
				}

				// Get the new block.
				$new_author_block = $this->replace_author_block_author( $block_content, $profile_post );
			}

			if ( $new_author_block && is_string( $new_author_block ) ) {
				return $new_author_block;
			}

			return $block_content;
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
}
