// Plugins.
const autoprefixer = require('autoprefixer');
const calc = require('postcss-calc');
const cssImport = require('postcss-import');
const customProps = require('postcss-custom-properties');
const customMedia = require('postcss-custom-media');
const nested = require('postcss-nested');
const focus = require('postcss-focus');

// Other imports.
const paths = require('./paths');
const cssVars = require('./css');
const flatten = require('../src/utils/flatten');

// Config.
module.exports = () => ({
  plugins: [
    cssImport({
      path: [
        paths.globalStyles,
      ],
    }), // Import files
    customProps({
      preserve: false,
      variables: flatten(cssVars),
    }),
    customMedia({
      extensions: cssVars.breakpoints,
    }),
    nested(), // Allow nested syntax.
    calc({
      mediaQueries: true,
    }),
    focus(),
    autoprefixer({
      flexbox: 'no-2009',
    }),
  ],
});
