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
        blockEditor: [
          path.join(appRoot, 'blockEditor.js'),
        ],
      };

    case 'development':
      return {
        main: [
          path.join(appRoot, 'index.js'),
        ],
        blockEditor: [
          path.join(appRoot, 'blockEditor.js'),
        ],
      };

    default:
      return {};
  }
};
