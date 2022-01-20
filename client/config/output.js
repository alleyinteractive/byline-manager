const { buildRoot } = require('./paths');

/**
 * Returns `output` configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - An `output` config object for the specified mode.
 */
module.exports = function getOutput(mode) {
  switch (mode) {
    case 'development':
      return {
        path: buildRoot,
        filename: '[name].bundle.js',
      };

    case 'production':
    default:
      return {
        clean: true,
        path: buildRoot,
        filename: '[name].[chunkhash:8].bundle.js',
      };
  }
};
