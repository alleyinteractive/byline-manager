const { buildRoot } = require('./paths');

/**
 * Returns `output` configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - An `output` config object for the specified mode.
 */
module.exports = function getOutput(mode) {
  switch (mode) {
    case 'production':
      return {
        path: buildRoot,
        filename: '[name].[chunkhash:8].bundle.js',
      };

    case 'development':
      return {
        path: buildRoot,
        publicPath: '//localhost:8080/',
        filename: '[name].bundle.js',
      };

    default:
      return {};
  }
};
