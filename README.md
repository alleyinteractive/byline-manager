Byline Manager
==============

[![Build Status](https://travis-ci.org/alleyinteractive/byline-manager.svg?branch=master)](https://travis-ci.org/alleyinteractive/byline-manager)

Manage an article's byline and author profiles.

## Requirements

Byline Manager requires PHP 7. It is developed for use on WordPress 4.9+, though it may also work on earlier versions.

## License

Byline Manager is released under the [GNU General Public License, Version 3](LICENSE.txt).

Byline Manager is a derivative work of [Daniel Bachhuber](https://danielbachhuber.com/)'s WordPress plugin _Bylines_ (licensed GPL-3.0) and [Automattic's Co-Authors Plus](https://github.com/Automattic/Co-Authors-Plus) (licensed GPL-2.0).

## Credits

Byline Manager is maintained by [Alley Interactive](https://alley.co/).

## Build Instructions

**Prerequisites:**
- [Node.js](https://nodejs.org/en/) - version 8.x
- [npm](https://www.npmjs.com/get-npm) - latest version

1. Navigate to the plugin and install NPM.
```
$ cd byline-manager
$ npm install
```

2. Build the assets:

`$ npm run dev` (development)

*When adding/editing a post, append `&byline-dev=on` to the URL.*
Ex: `https://www.example.com/wp-admin/post.php?post=1&action=edit&byline-dev=on`

`$ npm run build` (production)

## Maintaining the `production-built` Branch

The [`production-built`](https://github.com/newscorp-ghfb/byline-manager/tree/production-built) branch contains the built version of this plugin.

**After merging your pull request into the default branch, follow the steps below:**

*Eventually this will be automated by Jenkins.*

1. Pull down the latest `production` branch
```
$ git checkout production
(production) $ git pull
```

2. Merge `production` into the `production-built` branch
```
(production) $ git checkout production-built
(production-built) $ git pull origin production-built
(production-built) $ git merge --no-ff production
```

3. Build the assets
```
(production-built) $ npm i && npm run build
```

4. Commit your changes
```
(production-built) $ git commit -a -m "Updating build following PR #___"
(production-built) $ git push origin production-built
```
