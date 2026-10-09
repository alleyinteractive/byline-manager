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
use WP_Block;
use WP_Post;
use DOMDocument;
use DOMNode;
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
	 * @deprecated Unused. Each block reads the byline of its own post. Kept so code that sets it does not fail.
	 *
	 * @var array
	 */
	public static array $bylines = [];

	/**
	 * Boolean that controlled when to render the author blocks.
	 *
	 * @deprecated Unused. Rendering is tracked per post. Kept so code that sets it does not fail.
	 *
	 * @var bool
	 */
	public static bool $render = true;

	/**
	 * Posts whose bylines have been rendered, keyed by post ID.
	 *
	 * Only the first post author block of a post renders all of its bylines. Any further blocks for the same
	 * post render nothing, so authors are not shown twice.
	 *
	 * @var array<int, true>
	 */
	private array $rendered_posts = [];

	/**
	 * Initializes
	 *
	 * Sets up a filter on the rendered post author block.
	 */
	public function init(): void {
		add_filter( 'render_block_core/post-author', [ $this, 'append_and_filter_post_author_blocks' ], 10, 3 );
	}

	/**
	 * Filters the post author block data to use the bylines of the block's post, also duplicates the first post
	 * author block of a post as many times as necessary to output all the bylines.
	 *
	 * @param string         $block_content The block content.
	 * @param array          $parsed_block  The parsed block. Unused.
	 * @param WP_Block|mixed $block         The block instance, used to find the post the block is rendered for.
	 * @return string The block content.
	 */
	public function append_and_filter_post_author_blocks( $block_content, $parsed_block = [], $block = null ): string {
		$post_id  = $block instanceof WP_Block && ! empty( $block->context['postId'] )
			? (int) $block->context['postId']
			: (int) get_the_ID();
		$profiles = $this->get_byline_profiles( $post_id );

		// A post without a byline does not render an author block.
		if ( empty( $profiles ) ) {
			return '';
		}

		// Nothing to filter when the core block rendered nothing.
		if ( '' === trim( (string) $block_content ) ) {
			return (string) $block_content;
		}

		// The bylines of this post are already rendered by an earlier block. Anything returned here would print
		// after them.
		if ( isset( $this->rendered_posts[ $post_id ] ) ) {
			return '';
		}

		$this->rendered_posts[ $post_id ] = true;

		$additional_blocks = '';

		// Filter the block once per byline and append it, essentially duplicating it.
		foreach ( array_keys( $profiles ) as $index ) {
			$additional_blocks .= $this->filter_post_author_block( (string) $block_content, $index, $profiles );
		}

		return $additional_blocks;
	}

	/**
	 * Gets the byline entries for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array[] Byline entries, in order.
	 */
	private function get_byline_profiles( int $post_id ): array {
		if ( $post_id <= 0 ) {
			return [];
		}

		$byline = get_post_meta( $post_id, 'byline', true );

		if ( ! is_array( $byline ) || empty( $byline['profiles'] ) || ! is_array( $byline['profiles'] ) ) {
			return [];
		}

		return array_values( $byline['profiles'] );
	}

	/**
	 * Filters the author block data with byline info.
	 *
	 * @param string       $block_content The block content.
	 * @param int          $index         Used as an array key.
	 * @param array[]|null $profiles      Byline entries. Defaults to the entries of the current post.
	 *
	 * @return string The modified core author block.
	 */
	public function filter_post_author_block( string $block_content, int $index, ?array $profiles = null ): string {
		// Setup some empty variables.
		$byline_type      = '';
		$profile_post     = '';
		$text_byline      = '';
		$new_author_block = '';
		$profiles       ??= $this->get_byline_profiles( (int) get_the_ID() );

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
					$this->set_node_text( $anchor_node, $profile_post->post_title );
					$permalink = get_permalink( $profile_post->ID );

					if ( $permalink ) {
						$anchor_node->setAttribute( 'href', $permalink );
					}
				}
			} else {
				// Replace the author name.
				$this->set_node_text( $name_node, $profile_post->post_title );
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
			$this->set_node_text( $name_node, $name );
		}
		return $doc->saveHTML();
	}

	/**
	 * Replaces the content of a node with plain text.
	 *
	 * Setting `nodeValue` parses its value for entities, which empties the node when the text holds a bare
	 * ampersand. A text node keeps the text as given and the serializer escapes it. Names can arrive entity-encoded
	 * (`Smith &amp; Jones`) or as typed (`Smith & Jones`), so tags are stripped and entities decoded first.
	 *
	 * @param DOMNode $node Node to change.
	 * @param string  $text Text, which may contain entities.
	 */
	private function set_node_text( DOMNode $node, string $text ): void {
		$document = $node->ownerDocument; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( ! $document instanceof DOMDocument ) {
			return;
		}

		while ( $node->firstChild ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$node->removeChild( $node->firstChild ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		$node->appendChild(
			$document->createTextNode(
				html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
			)
		);
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
