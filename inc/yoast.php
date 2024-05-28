<?php
/**
 * Integrate with Yoast SEO
 *
 * @package Byline_Manager
 */

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use Byline_Manager\Models\TextProfile;
use Byline_Manager\Yoast\Profile_Schema;
use Byline_Manager\Yoast\TextProfile_Schema;
use Yoast\WP\SEO\Config\Schema_Types;
use Yoast\WP\SEO\Context\Meta_Tags_Context;
use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use Yoast\WP\SEO\Generators\Schema\Person;
use Yoast\WP\SEO\Presentations\Indexable_Presentation;

// These filters run early to try to give preference to existing integrations on sites using both plugins.
add_filter( 'wpseo_meta_author', __NAMESPACE__ . '\filter_wpseo_meta_author', 1, 2 );
add_filter( 'wpseo_enhanced_slack_data', __NAMESPACE__ . '\filter_wpseo_enhanced_slack_data', 1, 2 );
add_filter( 'wpseo_schema_graph_pieces', __NAMESPACE__ . '\filter_wpseo_schema_graph_pieces', 1, 2 );
add_filter( 'wpseo_schema_graph', __NAMESPACE__ . '\filter_wpseo_schema_graph', 1, 2 );

/**
 * Filter the Yoast SEO author meta tag.
 *
 * @param string                 $author_name  The article author's display name. Return empty to disable the tag.
 * @param Indexable_Presentation $presentation The presentation of an indexable.
 * @return string Filtered tag.
 */
function filter_wpseo_meta_author( $author_name, $presentation ): string {
	$id   = $presentation->context->post->ID;
	$type = $presentation->model->object_sub_type;

	if ( $id && $type && Utils::is_post_type_supported( $type ) ) {
		$author_name = get_the_byline( $id );
	}

	return $author_name;
}

/**
 * Filter the Yoast SEO enhanced data for sharing on Slack.
 *
 * @param array                  $data         The enhanced Slack sharing data.
 * @param Indexable_Presentation $presentation The presentation of an indexable.
 * @return array Filtered data.
 */
function filter_wpseo_enhanced_slack_data( $data, $presentation ): array {
	$id   = $presentation->context->post->ID;
	$type = $presentation->model->object_sub_type;

	if ( $id && $type && Utils::is_post_type_supported( $type ) ) {
		$byline = get_the_byline( $id );

		if ( $byline ) {
			$data['Written by'] = $byline;
		} else {
			unset( $data['Written by'] );
		}
	}

	return $data;
}

/**
 * Remove all 'Person' nodes from the Yoast SEO schema graph before adding ours.
 *
 * @param Abstract_Schema_Piece[] $schema_pieces The existing graph pieces.
 * @param Meta_Tags_Context       $context       An object with context variables.
 * @return Abstract_Schema_Piece[]
 */
function filter_wpseo_schema_graph_pieces( $schema_pieces, $context ) {
	if (
		'post' === $context->indexable->object_type
		&& Utils::is_post_type_supported( $context->indexable->object_sub_type )
	) {
		$schema_pieces = array_filter( $schema_pieces, fn ( $piece ) => ! $piece instanceof Person );
	}

	return $schema_pieces;
}

/**
 * Filters the Yoast SEO schema graph output.
 *
 * @param array             $graph   The graph to filter.
 * @param Meta_Tags_Context $context An object with context variables.
 * @return array
 */
function filter_wpseo_schema_graph( $graph, $context ) {
	if ( ! class_exists( Schema_Types::class ) || ! function_exists( 'YoastSEO' ) ) {
		return $graph;
	}

	if ( ! $graph ) {
		return $graph;
	}

	$schema_pieces = [];
	$schema_types  = new Schema_Types();
	$helpers       = YoastSeo()->helpers;

	/*
	 * It's easier to create the schema pieces and generate their output here, rather than filtering the schema
	 * pieces into 'filter_schema_graph_pieces', because Yoast allows only one node of each '@type' in the graph.
	 */
	if ( 'post' === $context->indexable->object_type && Utils::is_post_type_supported( $context->indexable->object_sub_type ) ) {
		foreach ( Utils::get_byline_entries_for_post( $context->indexable->object_id ) as $entry ) {
			if ( $entry instanceof Profile ) {
				$schema_pieces[] = new Profile_Schema( $entry, $helpers, $context );
			}

			if ( $entry instanceof TextProfile ) {
				$schema_pieces[] = new TextProfile_Schema( $entry, $helpers, $context );
			}
		}
	}

	$new_schema_nodes = [];
	$new_schema_ids   = [];

	foreach ( $schema_pieces as $schema_piece ) {
		if ( $schema_piece->is_needed() ) {
			$generated          = $schema_piece->generate();
			$new_schema_nodes[] = $generated;

			if ( isset( $generated['@id'] ) ) {
				$new_schema_ids[] = [ '@id' => $generated['@id'] ];
			}
		}
	}

	array_push( $graph, ...$new_schema_nodes );

	// Update 'author' property in article nodes to reference the IDs of our new schema objects.
	foreach ( $graph as $i => $node ) {
		if (
			isset( $node['@type'] )
			&& is_string( $node['@type'] )
			&& isset( $schema_types::ARTICLE_TYPES[ $node['@type'] ] )
		) {
			$graph[ $i ]['author'] = $new_schema_ids;
		}
	}

	return $graph;
}
