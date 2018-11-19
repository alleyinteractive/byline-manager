<?php
/**
 * Template tags
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Models\Profile;

/**
 * Renders the bylines display names, without links to their posts, or the
 * byline override if present.
 *
 * Equivalent to the_author() template tag.
 */
function the_byline() {
	echo get_the_byline();
}

/**
 * Gets the bylines display names, without links to their posts, or the byline
 * override if present.
 *
 * Equivalent to get_the_author() template tag.
 */
function get_the_byline() {
	return byline_render(
		Utils::get_byline_entries_for_post(),
		function( $entry ) {
			return strip_tags( $entry->display_name );
		}
	);
}

/**
 * Renders the profiles display names, with links to their posts, or the byline
 * override if present.
 *
 * Equivalent to the_author_posts_link() template tag.
 */
function the_byline_posts_links() {
	echo get_the_byline_posts_links();
}

/**
 * Renders the profiles display names, with links to their posts, or the byline
 * override if present.
 */
function get_the_byline_posts_links() {
	return byline_render(
		Utils::get_byline_entries_for_post(),
		function( $entry ) {
			$args = [
				'before_html' => '',
				'href' => $entry->link,
				'rel' => 'author',
				// translators: Posts by a given author.
				'title' => sprintf( __( 'Posts by %1$s', 'byline-manager' ), $entry->display_name ),
				'class' => 'author url fn',
				'text' => $entry->display_name,
				'after_html' => '',
			];

			/**
			 * Arguments for determining the display of profiles with posts links
			 *
			 * @param array  $args   Arguments determining the rendering of the profile.
			 * @param Byline $entry The profile or text profile to be rendered.
			 */
			$args = apply_filters( 'bylines_posts_links', $args, $entry );
			if ( empty( $args['href'] ) ) {
				$single_item = $args['text'];
			} else {
				$single_item = sprintf(
					'<a href="%1$s" title="%2$s" class="%3$s" rel="%4$s">%5$s</a>',
					esc_url( $args['href'] ),
					esc_attr( $args['title'] ),
					esc_attr( $args['class'] ),
					esc_attr( $args['rel'] ),
					esc_html( $args['text'] )
				);
			}
			return $args['before_html'] . $single_item . $args['after_html'];
		}
	);
}

/**
 * Renders the profiles display names, with their website link if it exists, or
 * the byline override if present.
 *
 * Equivalent to the_author_link() template tag.
 */
function the_byline_links() {
	echo get_the_byline_links();
}

/**
 * Renders the profiles display names, with their website link if it exists, or
 * the byline override if present.
 */
function get_the_byline_links() {
	return byline_render(
		Utils::get_byline_entries_for_post(),
		function( $entry ) {
			if ( $entry instanceof Models\Profile && $entry->user_url ) {
				return sprintf(
					'<a href="%s" title="%s" rel="external">%s</a>',
					esc_url( $entry->user_url ),
					// Translators: refers to the profile's website.
					esc_attr( sprintf( __( 'Visit %s&#8217;s website', 'byline-manager' ), $entry->display_name ) ),
					$entry->display_name
				);
			} else {
				return $entry->display_name;
			}
		}
	);
}

/**
 * Display byline, according to arguments provided.
 *
 * @param array    $byline          Set of byline to display.
 * @param callable $render_callback Callback to return rendered byline.
 * @param array    $args            Arguments to affect display.
 */
function byline_render( $byline, $render_callback, $args = [] ) {
	if ( empty( $byline )
		|| empty( $render_callback )
		|| ! is_callable( $render_callback ) ) {
		return '';
	}
	$defaults = [
		'between'           => ', ',
		'between_last_two'  => __( ' and ', 'byline-manager' ),
		'between_last_many' => __( ', and ', 'byline-manager' ),
	];
	$args = array_merge( $defaults, $args );
	$total = count( $byline );
	$current = 0;
	$output = '';
	foreach ( $byline as $author ) {
		$current++;
		if ( $current > 1 ) {
			if ( $current === $total ) {
				if ( 2 === $total ) {
					$output .= $args['between_last_two'];
				} else {
					$output .= $args['between_last_many'];
				}
			} elseif ( $total >= 2 ) {
				$output .= $args['between'];
			}
		}
		$output .= $render_callback( $author );
	}
	return $output;
}
