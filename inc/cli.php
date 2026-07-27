<?php
/**
 * WP-CLI commands
 *
 * @package Byline_Manager
 */

declare(strict_types=1);

namespace Byline_Manager;

use Byline_Manager\Models\Profile;
use WP_CLI;
use WP_Query;

use function WP_CLI\Utils\get_flag_value;

/**
 * Register the plugin's WP-CLI commands.
 */
function register_cli_commands(): void {
	WP_CLI::add_command( 'byline-manager migrate-user-meta-to-blog-prefix', __NAMESPACE__ . '\migrate_user_meta_to_blog_prefix' );
}
add_action( 'cli_init', __NAMESPACE__ . '\register_cli_commands' );

/**
 * Move the user meta linking users to profiles onto the blog-prefixed meta key.
 *
 * User meta is shared across every site in a multisite network, so the unprefixed
 * 'profile_id' key can only describe a user's profile on one site at a time. The plugin now
 * reads and writes a blog-prefixed key instead, and falls back to the unprefixed key for links
 * made before the change.
 *
 * This command visits every site in the network and rewrites the link for each profile that
 * names a user account, using the profile's own 'user_id' post meta as the source of truth. It
 * then deletes the old unprefixed rows.
 *
 * ## OPTIONS
 *
 * [--dry-run]
 * : Don't persist changes.
 *
 * ## EXAMPLES
 *
 *     # Preview the migration.
 *     $ wp byline-manager migrate-user-meta-to-blog-prefix --dry-run
 *
 *     # Run the migration.
 *     $ wp byline-manager migrate-user-meta-to-blog-prefix
 *
 * @param string[] $args       Positional arguments.
 * @param string[] $assoc_args Associative arguments.
 */
function migrate_user_meta_to_blog_prefix( $args, $assoc_args ): void {
	global $wpdb, $wp_object_cache;

	$dry_run  = get_flag_value( $assoc_args, 'dry-run', false );
	$execute  = ! $dry_run;
	$site_ids = [ get_current_blog_id() ];

	if ( is_multisite() ) {
		$site_ids = get_sites(
			[
				'fields' => 'ids',
				'number' => 0,
			],
		);
	}

	$migrated = 0;

	foreach ( $site_ids as $site_id ) {
		$site_id = (int) $site_id;

		if ( is_multisite() ) {
			switch_to_blog( $site_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog
		}

		try {
			$count = 0;
			$paged = 1;

			do {
				$query = new WP_Query(
					[
						'ignore_sticky_posts'    => true,
						'meta_compare'           => 'EXISTS',
						'meta_key'               => 'user_id',
						'paged'                  => $paged,
						'post_status'            => array_keys( get_post_stati() ),
						'post_type'              => PROFILE_POST_TYPE,
						'posts_per_page'         => 100,
						'update_post_term_cache' => false,
					],
				);

				foreach ( $query->posts as $post ) {
					$profile = Profile::get_by_post( $post );

					if ( ! $profile instanceof Profile ) {
						continue;
					}

					$user_id = $profile->get_linked_user_id();

					if ( ! ( $user_id > 0 ) ) {
						WP_CLI::debug( sprintf( 'Profile %d has a no linked user ID. Skipping.', $profile->post_id ) );

						continue;
					}

					if ( $execute ) {
						$profile->update_user_link( $user_id );
					}

					++$count;

					WP_CLI::debug( sprintf( '%s profile %d to user ID %d.', $dry_run ? 'Would link' : 'Linked', $profile->post_id, $user_id ) );
				}

				++$paged;

				// Reset query cache.
				$wpdb->queries = [];

				// Reset object cache.
				if ( is_object( $wp_object_cache ) ) {
					if ( isset( $wp_object_cache->group_ops ) ) {
						$wp_object_cache->group_ops = [];
					}

					if ( isset( $wp_object_cache->memcache_debug ) ) {
						$wp_object_cache->memcache_debug = [];
					}

					if ( isset( $wp_object_cache->cache ) ) {
						$wp_object_cache->cache = [];
					}

					if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
						$wp_object_cache->__remoteset();
					}
				}
			} while ( $paged <= $query->max_num_pages );

			WP_CLI::log( sprintf( 'Site %d: %d profile(s) with a linked user.', $site_id, $count ) );

			$migrated += $count;
		} finally {
			if ( is_multisite() ) {
				restore_current_blog();
			}
		}
	}

	WP_CLI::log(
		sprintf(
			'%s %d user link(s) across %d site(s).',
			$dry_run ? 'Would rewrite' : 'Rewrote',
			$migrated,
			count( $site_ids ),
		),
	);

	$legacy_user_ids = get_users(
		[
			'blog_id'      => 0,
			'fields'       => 'ids',
			'meta_compare' => 'EXISTS',
			'meta_key'     => 'profile_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		],
	);

	if ( count( $legacy_user_ids ) === 0 ) {
		WP_CLI::success( 'No unprefixed profile_id user meta remains.' );
	} elseif ( $dry_run ) {
		WP_CLI::success( sprintf( 'Would delete the unprefixed profile_id meta for %d user(s).', count( $legacy_user_ids ) ) );
	} else {
		delete_metadata( 'user', 0, 'profile_id', '', true );

		WP_CLI::success( sprintf( 'Deleted the unprefixed profile_id meta for %d user(s).', count( $legacy_user_ids ) ) );
	}
}
