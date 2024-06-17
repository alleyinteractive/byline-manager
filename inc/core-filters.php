<?php
/**
 * Core filter integration
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Automatically integrate the byline into posts via the `the_author` filter.
 *
 * @param string $author_name Author display_name as provided by `the_author`.
 * @return string Byline if applicable, otherwise author name.
 */
function auto_integrate_byline( $author_name ): string {
	if ( ! Utils::is_post_type_supported() ) {
		return $author_name;
	}

	if ( is_feed() ) {
		$profiles = Utils::get_byline_entries_for_post();
		if ( ! empty( $profiles[0] ) ) {
			return $profiles[0]->display_name;
		}
	}

	return get_the_byline();
}
add_filter( 'the_author', __NAMESPACE__ . '\auto_integrate_byline' );

/**
 * Automatically integrate the byline into the `the_author_posts_link` filter.
 *
 * @param string $link The HTML link to the author's posts archive.
 * @return string Updated link, if applicable.
 */
function auto_integrate_byline_posts_links( $link ): string {
	if ( ! Utils::is_post_type_supported() ) {
		return $link;
	}

	return get_the_byline_posts_links();
}
add_filter( 'the_author_posts_link', __NAMESPACE__ . '\auto_integrate_byline_posts_links' );

/**
 * Add additional dc:creator nodes to RSS feeds for additional authors.
 */
function rss_add_additional_authors(): void {
	$profiles = Utils::get_byline_entries_for_post();
	if ( count( $profiles ) > 1 ) {
		// The first author was already output in auto_integrate_byline().
		array_shift( $profiles );

		foreach ( $profiles as $profile ) {
			echo '      <dc:creator><![CDATA[' . esc_html( $profile->display_name ) . "]]></dc:creator>\n";
		}
	}
}
add_action( 'rss2_item', __NAMESPACE__ . '\rss_add_additional_authors' );

/**
 * Filter the URL to the author's posts page.
 *
 * @param string $link      The URL to the author's page.
 * @param int    $author_id The author's id.
 * @return string Author's posts URL.
 */
function override_author_link( $link, $author_id ): string {
	$profile_id = absint( get_user_meta( $author_id, 'profile_id', true ) );
	if ( $profile_id ) {
		return get_permalink( $profile_id ) ?: '';
	} else {
		return '';
	}
}
add_filter( 'author_link', __NAMESPACE__ . '\override_author_link', 10, 2 );

/**
 * Disable the core author rewrite rules
 */
add_filter( 'author_rewrite_rules', '__return_empty_array' );

/**
 * Unset trackback, attachment, and comment rewrites on PROFILE_POST_TYPE
 *
 * @param array $rules Existing rewrite rules to be filtered.
 * @return array $rules New rewrite rules.
 */
function unset_rewrites( $rules ): array {
	$profile_post_type_data = get_post_type_object( PROFILE_POST_TYPE );
	$profile_post_type_slug = $profile_post_type_data->rewrite['slug'];
	foreach ( $rules as $rule => $rewrite ) {
		if ( preg_match( '/^' . $profile_post_type_slug . '.*(trackback)/', $rule ) || preg_match( '/^' . $profile_post_type_slug . '.*(attachment)/', $rule ) || preg_match( '/^' . $profile_post_type_slug . '.*(comment)/', $rule ) ) {
			unset( $rules[ $rule ] );
		}
	}
	return $rules;
}
add_filter( 'rewrite_rules_array', __NAMESPACE__ . '\unset_rewrites' );

/**
 * Callback function that checks if the rendered block is the `core/post-author`
 * and then gets the byline meta of the current post to then pass along to `replace_author_block_author()`
 *
 * @param string               $block_content The block content.
 * @param array<string, mixed> $block The full block, including name and attributes.
 *
 * @return string The unfiltered or filtered block.
 */
function filter_post_author_block( string $block_content, array $block ): string {
	if ( 'core/post-author' === $block['blockName'] ) {
		global $post;

		$profile_post = '';
		$meta         = get_post_meta( $post->ID, 'byline', true );

		if ( is_array( $meta ) && ! empty( $meta['profiles'] ) ) {
			foreach ( $meta['profiles'] as $profile ) {
				// TODO: Handle instance where theres multiple profiles.
				if ( 'byline_id' === $profile['type'] && ! empty( $profile['atts']['post_id'] ) ) {
					$profile_post = get_post( $profile['atts']['post_id'] );
				}
			}
		}

		// Check that the profile is a WP_Post.
		if ( is_null( $profile_post ) || is_string( $profile_post ) || ! is_a( $profile_post, 'WP_Post' ) ) {
			return $block_content;
		}

		// Get the new block.
		$new_author_block = replace_author_block_author( $block_content, $profile_post );

		if ( $new_author_block && is_string( $new_author_block ) ) {
			return $new_author_block;
		}

		return $block_content;
	}
	return $block_content;
}
add_filter( 'render_block', __NAMESPACE__ . '\filter_post_author_block', 10, 2 );

/**
 * Replaces the author in the author block.
 *
 * @param string   $html Author block html.
 * @param \WP_Post $profile_post Post object of the profile post type.
 *
 * @return bool|string Filtered author block.
 */
function replace_author_block_author( string $html, \WP_Post $profile_post ): bool|string {
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
