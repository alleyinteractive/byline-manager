# Byline Manager

[![Testing Suite](https://github.com/alleyinteractive/byline-manager/actions/workflows/all-pr-tests.yml/badge.svg)](https://github.com/alleyinteractive/byline-manager/actions/workflows/all-pr-tests.yml)

Byline Manager is a WordPress plugin that replaces the built-in single-author
field with a flexible, multi-author byline system. It introduces **author
profiles** as a dedicated post type and lets editors assign any combination of
profiles and free-form text credits to any post — all without touching a
WordPress user account.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
  - [Managing Profiles](#managing-profiles)
  - [Assigning Bylines](#assigning-bylines)
  - [Displaying Bylines in Themes](#displaying-bylines-in-themes)
- [Template Tags](#template-tags)
- [Hooks & Filters](#hooks--filters)
- [REST API](#rest-api)
- [Integrations](#integrations)
  - [Block Editor](#block-editor)
  - [Classic Editor](#classic-editor)
  - [WPGraphQL](#wpgraphql)
  - [Yoast SEO](#yoast-seo)
- [Developer Reference](#developer-reference)
  - [Models](#models)
  - [Utility Methods](#utility-methods)
- [Development](#development)
- [License](#license)
- [Credits](#credits)

---

## Features

- **Multiple authors per post** — assign as many author profiles as needed to a
  single article, with full drag-and-drop reordering.
- **Author profiles** — a dedicated `profile` post type stores display name,
  biography, profile photo, website URL, and other metadata independently of
  WordPress user accounts.
- **Text-only byline entries** — add free-form credit lines (e.g. "Photography
  by Jane Smith" or "Translated by …") that sit alongside profile links without
  requiring a WordPress user or profile post.
- **Block Editor sidebar panel** — a native Gutenberg sidebar panel with
  real-time author search, add/remove, and drag-and-drop reorder.
- **Classic Editor metabox** — fully functional byline metabox for sites still
  using the Classic Editor.
- **User–profile linking** — optionally link a WordPress user account to a
  profile post, enabling author archive redirects, automatic byline
  pre-population on new posts, and unified identity across the site.
- **Core WordPress filter integration** — automatically overrides `the_author`,
  `the_author_posts_link`, author archive URLs, and RSS `dc:creator` tags so
  existing themes and plugins display bylines without any extra code.
- **Gutenberg core author block support** — the built-in Post Author block is
  automatically duplicated once per byline entry so block-theme templates work
  out of the box.
- **REST API** — search profiles and users, and hydrate byline data via the
  `byline-manager/v1` namespace.
- **WPGraphQL support** — query profiles and byline data when WPGraphQL is
  active.
- **Yoast SEO integration** — byline data is automatically surfaced in Yoast
  meta-author fields, Slack sharing data, and the structured-data schema graph.
- **Filterable post-type support** — bylines are enabled for every post type
  that declares `author` support by default, and can be narrowed or extended
  via a single filter.

---

## Requirements

- **PHP:** 8.1 or higher
- **WordPress:** 6.3 or higher (may work on earlier versions)

---

## Installation

1. Download or clone this repository into your `wp-content/plugins/` directory.
2. Activate **Byline Manager** from the *Plugins* screen in WordPress admin.
3. Bylines are automatically enabled for every post type that declares
   `author` support. See [Hooks & Filters](#hooks--filters) to change this.

---

## Usage

### Managing Profiles

1. In the WordPress admin sidebar, navigate to **Profiles**.
2. Click **Add New** to create an author profile.
3. Fill in the profile's display name (post title), biography (post content),
   featured image (profile photo), and any custom metadata (first name, last
   name, email, website URL).
4. Optionally link the profile to a WordPress user account via the **User
   Account** metabox on the profile edit screen. This enables author archive
   redirects and automatic byline pre-population.
5. Publish the profile.

### Assigning Bylines

**Block Editor:**

Open a post and expand the **Byline** panel in the document sidebar. Use the
search box to find and add profiles. Add free-form text credits using the text
entry field. Drag entries to reorder them, or click the remove button to delete
an entry.

**Classic Editor:**

A **Byline** metabox appears below the post editor. It provides the same
search, add, reorder, and remove controls.

### Displaying Bylines in Themes

The plugin automatically filters `the_author` and `the_author_posts_link`, so
many themes will display bylines without any changes. For explicit control, use
the [template tags](#template-tags) described below.

---

## Template Tags

All template tags accept an optional `$post` argument (defaults to the current
post in The Loop).

| Tag | Description |
|-----|-------------|
| `get_the_byline( $post )` | Returns byline as a plain-text comma-separated string. |
| `the_byline( $post )` | Echoes the plain-text byline. |
| `get_the_byline_posts_links( $post )` | Returns byline as HTML with links to each author's profile archive page. |
| `the_byline_posts_links( $post )` | Echoes the byline with profile archive links. |
| `get_the_byline_links( $post )` | Returns byline as HTML with links to each author's website URL (if set). |
| `the_byline_links( $post )` | Echoes the byline with author website links. |

**Example — display byline with links to profile pages:**

```php
<span class="byline">
    By <?php the_byline_posts_links(); ?>
</span>
```

---

## Hooks & Filters

### `byline_manager_supported_post_types`

Filters the list of post types that support bylines. Defaults to all post types
that declare `author` support.

```php
add_filter( 'byline_manager_supported_post_types', function( array $post_types ): array {
    // Limit bylines to posts and a custom 'article' post type.
    return [ 'post', 'article' ];
} );
```

### `byline_manager_remove_author_support`

Filters the list of post types from which standard `author` support should be
removed (so only the byline field appears in the editor).

### `byline_manager_modify_post_edit_columns`

Return `false` to prevent Byline Manager from replacing the *Author* column in
post list tables.

### `byline_manager_edit_columns_post_types`

Filters which post types get the byline *Authors* column in list tables.

### `byline_manager_post_byline_meta`

Filters the raw byline meta array immediately before it is saved to a post.

```php
add_filter( 'byline_manager_post_byline_meta', function( array $byline_meta, int $post_id ): array {
    // Modify $byline_meta['profiles'] here.
    return $byline_meta;
}, 10, 2 );
```

### `byline_manager_auto_set_user_profile`

Return `false` to prevent Byline Manager from automatically adding the
currently logged-in user's linked profile as a byline on new posts.

### `byline_manager_rewrite_slug`

Filters the URL slug used for profile archive pages. Defaults to the value of
the WordPress `author_base` option (typically `author`).

### `bylines_posts_links`

Filters the final HTML string produced by `get_the_byline_posts_links()`.

### `byline_manager_localized_data`

Filters the data object passed to the editor JavaScript bundle, allowing custom
data to be injected into the block editor panel.

---

## REST API

Byline Manager registers endpoints under the `byline-manager/v1` namespace.
All endpoints require the user to be logged in.

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/authors` | `GET` | Search author profiles by keyword. Accepts `?s=` query parameter. |
| `/users` | `GET` | Search WordPress users for linking to profiles. Accepts `?s=` query parameter. |
| `/hydrateProfiles` | `POST` | Fetch full profile data for a set of byline entries. Automatically includes the current user's linked profile when creating a new post. |

The `byline` post meta field is also registered with the REST API for all
supported post types, allowing byline data to be read and written via the
standard Posts endpoints.

---

## Integrations

### Block Editor

A **Byline** sidebar panel is registered as a `PluginDocumentSettingPanel`
slot. It uses a Redux data store (`byline-manager/store`) to manage state and
communicates with the REST API for profile search and hydration.

The plugin also filters `render_block_core/post-author` to duplicate the core
Post Author block once for each byline entry, ensuring block-based themes
render all authors automatically.

### Classic Editor

A standard WordPress metabox is added to all supported post types. It loads
the same React interface used in the block editor sidebar.

### WPGraphQL

When [WPGraphQL](https://www.wpgraphql.com/) is active, Byline Manager
registers the following types:

| Type | Description |
|------|-------------|
| `TextProfile` | Represents a text-only byline entry. |
| `ProfileTypes` | Union type of `Profile` and `TextProfile`. |
| `BylineTextEnum` | Format argument for byline text (`text`, `links`, `post_links`). |
| `Byline` | Object with a `bylineText` field that accepts a format argument. |

Profile posts are also queryable via the `profiles` connection.

### Yoast SEO

When Yoast SEO is active, Byline Manager automatically:

- Populates the `wpseo_meta_author` field with the byline string.
- Adds byline data to the Slack-sharing enhanced data (`wpseo_enhanced_slack_data`).
- Injects `ProfileSchema` and `TextProfileSchema` pieces into the structured-data graph.

---

## Developer Reference

### Models

#### `Byline_Manager\Models\Profile`

Represents a full author profile (backed by a `profile` post).

```php
use Byline_Manager\Models\Profile;

// Get a profile by its post ID.
$profile = Profile::get_by_post( $post_id );

// Create a new profile.
$profile = Profile::create( [ 'post_title' => 'Jane Smith' ] );

// Create a profile from an existing WordPress user.
$profile = Profile::create_from_user( $user_id );

// Useful properties.
$profile->display_name;           // Profile post title.
$profile->description;            // Profile post content / bio.
$profile->link;                   // Profile archive permalink.
$profile->post_id;                // Profile post ID.
$profile->term_id;                // Term ID in the hidden 'byline' taxonomy (also accessible as $profile->byline_id).
$profile->user_url;               // Author website URL.

// Link a WordPress user to this profile.
$profile->update_user_link( $user_id );

// Get the linked WordPress user ID (or false).
$profile->get_linked_user_id();
```

#### `Byline_Manager\Models\TextProfile`

Represents a text-only byline entry.

```php
use Byline_Manager\Models\TextProfile;

$text_profile = TextProfile::create( [ 'text' => 'Photography by Alley' ] );

$text_profile->display_name;    // The credit text.
$text_profile->link;            // Empty string (no archive page).
$text_profile->atts;            // Raw attributes array.
```

### Utility Methods

`Byline_Manager\Utils` provides static helpers for working with bylines
programmatically.

```php
use Byline_Manager\Utils;

// Retrieve the list of post types that support bylines.
Utils::get_supported_post_types();

// Check whether a specific post type supports bylines.
Utils::is_post_type_supported( 'post' );

// Get the raw byline meta array for a post.
Utils::get_byline_meta_for_post( $post );

// Get an array of Profile / TextProfile objects for a post.
Utils::get_byline_entries_for_post( $post );

// Overwrite the byline for a post with a new meta array.
Utils::set_post_byline( $post_id, $byline_meta );

// Assign bylines by an array of byline term IDs.
Utils::assign_bylines_to_post( $post_id, $byline_ids );

// Get a Profile's post ID from its byline taxonomy term ID.
Utils::get_profile_id_by_byline_id( $term_id );

// Get or create a byline profile by slug.
Utils::get_or_create_byline( $slug, $title, $content );
```

---

## Development

### Prerequisites

- Node.js 20 (see `.nvmrc`)
- Composer

### Frontend

```bash
npm install          # Install JavaScript dependencies.
npm run build        # Production build.
npm run dev          # Watch mode for development.
npm run lint         # Lint JavaScript and SCSS.
npm run test         # Run Jest unit tests.
```

### Backend

```bash
composer install     # Install PHP dependencies.
composer test        # Run PHPCS, PHPStan, and PHPUnit.
```

Individual PHP quality tools:

```bash
composer phpcs       # PHP_CodeSniffer code-style check.
composer phpstan     # PHPStan static analysis.
composer phpunit     # PHPUnit unit tests.
```

---

## License

Byline Manager is released under the
[GNU General Public License, Version 3](LICENSE.txt).

Byline Manager is a derivative work of
[Daniel Bachhuber](https://danielbachhuber.com/)'s WordPress plugin _Bylines_
(licensed GPL-3.0) and
[Automattic's Co-Authors Plus](https://github.com/Automattic/Co-Authors-Plus)
(licensed GPL-2.0).

## Credits

Byline Manager is maintained by [Alley Interactive](https://alley.com/).
