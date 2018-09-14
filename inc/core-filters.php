<?php
/**
 * Core filter integration
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

/**
 * Automatically integrate the byline into posts via the `the_author` filter.
 *
 * @param string $author_name Author display_name as provided by `the_author`.
 * @return string Byline if applicable, otherwise author name.
 */
function auto_integrate_byline( $author_name ) {
	if ( ! Utils::is_post_type_supported() ) {
		return $author_name;
	}

	if ( is_feed() ) {
		// If the post uses an override, this will be empty.
		$profiles = Utils::get_profiles_for_post();
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
function auto_integrate_byline_posts_links( $link ) {
	if ( ! Utils::is_post_type_supported() ) {
		return $link;
	}

	return get_the_byline_posts_links();
}
add_filter( 'the_author_posts_link', __NAMESPACE__ . '\auto_integrate_byline_posts_links' );

/**
 * Add additional dc:creator nodes to RSS feeds for additional authors.
 */
function rss_add_additional_authors() {
	$profiles = Utils::get_profiles_for_post();
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
function override_author_link( $link, $author_id ) {
	$profile_id = absint( get_user_meta( $author_id, 'profile_id', true ) );
	if ( $profile_id ) {
		return get_permalink( $profile_id );
	} else {
		return '';
	}
}
add_filter( 'author_link', __NAMESPACE__ . '\override_author_link', 10, 2 );
