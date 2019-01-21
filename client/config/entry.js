const path = require('path');
const { appRoot } = require('./paths');
const glob = require('glob');

/**
 * Returns `entry` configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - An `entry` config object for the specified mode.
 *
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - An `entry` config object for the specified mode.
 */
module.exports = function getEntry(mode) {
  switch (mode) {
    case 'production':
      return {
        // Gutenberg block entry points
        blocks: glob.sync('./blocks/*/client/src/index.js'),
        'blocks-style':
          glob.sync('./blocks/*/client/src/style.scss'),
      };

    case 'development':
      return {
        bylinedev: glob.sync('./blocks/*/client/src/index.js'),
        'blocks-styles':
          glob.sync('./blocks/*/client/src/style.scss'),
      };

    default:
      return {};
  }
};
