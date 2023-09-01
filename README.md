# Package README Standards

Byline Manager is a WordPress plugin for creating author profiles and managing author bylines on posts. It provides two notable advantages over the default WordPress behavior for authorship:

1. Authors do not need to be WordPress users. Authors can be Profiles, which are a custom post type, or text-only bylines.
2. Multiple authors can be assigned to a WordPress post.

Some of its other features include:

- Support for both the block editor (Gutenberg) and the classic editor
- Replacement of standard author pages with Byline Manager Profile pages at the same URL, allowing author pages to exist for authors who aren't WordPress users
- GraphQL support

## Background

WordPress core functionality provides limited support for managing authorship, and couples it tightly to WordPress users. Byline Manager was born out of a need to have author management that was decoupled from WordPress users while also providing first-class block editor support.

## Releases

This project follows [Semantic Versioning](https://semver.org/). Releases are created on an as-needed basis.

### Install

It is recommended to install this plugin using Composer, leveraging WPackagist. To do so, add the following to your `composer.json` file:

```json
{
	"repositories":[
		{
			"type":"composer",
			"url":"https://wpackagist.org",
			"only": [
				"wpackagist-plugin/*",
				"wpackagist-theme/*"
			]
		}
	],
	"require": {
		"alleyinteractive/byline-manager": "^0.2.0"
	}
}
```

Then, run `composer install` to install the plugin.

### Use

Once installed, activate the plugin from the WordPress Plugins screen. You can manage author profiles using the newly created Profiles menu item in the WordPress admin, and attach profiles or text-only bylines to posts using the post sidebar (block editor) or the Publish metabox (classic editor).

### From Source

To work on this plugin locally, you first need to clone it. If you plan on contributing back to the plugin, you should first fork it into your own GitHub account, then clone it locally.

Working on this plugin requires PHP >= 8.0, Composer, and Node 18 with NPM 9.

> Although your local development environment can use a version of PHP that is higher than 8.0, code written for the plugin needs to support PHP 8.0, so features that are exclusive to PHP versions greater than 8.0 cannot be used.

Once you have cloned the repository, you need to install PHP dependencies via Composer:

```sh
composer install
```

Then, you need to install JavaScript dependencies via NPM:

```sh
npm ci
```

> If you attempt to install dependencies or run npm commands while using a version of node other than 18 or npm other than 9, the command will error out thanks to the use of the `check-node-version` package to ensure that the correct versions are always used. The plugin ships with an `.nvmrc` file for use with [NVM](https://github.com/nvm-sh/nvm). If you have `nvm` installed, you can run `nvm use || nvm install` from within the plugin directory to ensure you are running the correct version of node. Note that npm 9 shipped with a later version of node 18, so if you are running an earlier version of node 18, you may need to upgrade in order to get npm 9.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Development Process

See instructions above on installing from source. Pull requests are welcome from the community and will be considered for inclusion.

### Contributing

See [our contributor guidelines](CONTRIBUTING.md) for instructions on how to contribute to this open source project.

## Project Structure

This project leverages `wp-scripts` to build assets for WordPress for both the block editor and the classic editor. Packages are installed locally for the sake of code completion, but are mapped to WordPress script globals using the Dependency Extraction Webpack Plugin, which ships with `wp-scripts`.

PHP code goes in `inc`, and JavaScript code goes in `src`.

## Related Efforts

Byline Manager integrates with [WPGraphQL](https://www.wpgraphql.com/), if it is installed. WPGraphQL is not a dependency of Byline Manager, and Byline Manager can be used without it.

Byline Manager is a derivative work of [Daniel Bachhuber](https://danielbachhuber.com/)'s WordPress plugin _Bylines_ (licensed GPL-3.0) and [Automattic's Co-Authors Plus](https://github.com/Automattic/Co-Authors-Plus) (licensed GPL-2.0).

## Maintainers

- [Alley](https://github.com/alleyinteractive)

![Alley logo](https://avatars.githubusercontent.com/u/1733454?s=200&v=4)

### Contributors

Thanks to all of the [contributors](CONTRIBUTORS.md) to this project.

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
