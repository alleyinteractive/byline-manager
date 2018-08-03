# Scripts
* `npm run build` - Build the app for a production environment.
* `npm run watch` - Run webpack in watch mode, enabling watching js and style updates.
* `npm run dev` - Run webpack-dev-server, hot module reloadingfor js and style updates.
* `npm run lint` - Lint JS files with ESLint.
* `npm run test` - Run test suite.
* `npm run test:watch` - Rerun test suit when files change.

## Setup
1. `npm install`

## Development
After running `npm run dev`, add the webpack dev server query flag to your URL: [devserver_base_url] + `/wp-admin/admin.php?page=byline-manager&fe-dev=1`

This will load files from the webpack server and automatically reload in the admin.
