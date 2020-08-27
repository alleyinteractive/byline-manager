const path = require('path');
const { appRoot } = require('./paths');

/**
 * Returns `entry` configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - An `entry` config object for the specified mode.
 */
module.exports = function getEntry(mode) {
  switch (mode) {
    case 'production':
      return {
        main: [
          path.join(appRoot, 'index.js'),
        ],
        blocks: [
          path.join(appRoot, 'blocks.js'),
        ],
      };

    case 'development':
      return {
        dev: [
          path.join(appRoot, 'index.js'),
        ],
      };

    default:
      return {};
  }
};
