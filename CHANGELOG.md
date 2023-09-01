# Changelog

All notable changes to `Byline Manager` will be documented in this file.

## Unreleased

- Enhancement: Adds custom slotfills to allow third-party developers to extend Byline Manager.
- Enhancement: Allows use of postmeta to link posts instead of taxonomy.
- Enhancement: REST profile query now returns 10 profiles instead of 5 using the WordPress standard search ordering, which defaults to relevance.
- Enhancement: Use declare(strict_types=1) for better type safety.
- Enhancement: Adds the byline_manager_localized_data filter to allow filtering of the localized data passed to the script.
- Fix: Adds support for custom-fields to the Byline post type, allowing addition of postmeta.
- Fix: Hide the BylineList slotfill component if there are no bylines selected.

## 0.2.0 - 2023-08-17

- Enhancement: Adds support for multiple text-only bylines in addition to Profiles.
- Enhancement: Adds post counts to profiles list.
- Enhancement: Sets rewrite rules on the Byline post type to match core author rules, and unsets core author rewrites.
- Enhancement: Adds Gutenberg support via a slotfill.
- Enhancement: Adds helper functions for getting or creating a byline by slug and title and assigning bylines to a post.
- Enhancement: Adds the byline_manager_get_profile_data_for_meta_box filter to allow profile data to be filtered for use in a metabox.
- Enhancement: Refactors Gutenberg components to use a custom store.
- Enhancement: Automatically sets byline to current user if current user has a byline associated with their login and no other bylines are set.
- Enhancement: Loads dependencies from those packaged with WordPress using the Dependency Extraction Webpack plugin rather than the wp global.
- Enhancement: Adds GraphQL support.
- Enhancement: Allow editing profiles using the block editor.
- Enhancement: Upgrade to Mantle Testkit.
- Fix: Adds labels for the featured image metabox and the post type labels added in WordPress 5.0.
- Fix: Updates API URLs to use rest_url instead of home_url.
- Fix: Ensures all REST endpoints have a permissions callback to prevent _doing_it_wrong calls in WP >= 5.5.
- Fix: Changes type in composer.json to wordpress-plugin so installations via Composer will work properly.
- Fix: Allow saving an empty set of bylines.
- Fix: Prevents querying Elasticsearch for bylines, as Profile posts may not be indexed in Elasticsearch, and this would cause the search to fail.
- Fix: Resolves a bug with sortable profiles no longer being sortable.

## 0.1.0 - 2018-11-20

- Initial release.
