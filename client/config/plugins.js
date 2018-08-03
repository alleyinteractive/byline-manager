const path = require('path');
const webpack = require('webpack');
const CleanPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const StylelintPlugin = require('stylelint-webpack-plugin');
const paths = require('./paths');

/**
 * Returns `plugins` configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Array} - A `plugins` config array for the specified mode.
 */
module.exports = function getPlugins(mode) {
  switch (mode) {
    case 'production':
      return [
        new webpack.NoEmitOnErrorsPlugin(),
        new CleanPlugin(['build'], {
          root: paths.clientRoot,
        }),
        new StylelintPlugin({
          configFile: path.join(paths.config, 'stylelint.config.js'),
        }),
        new MiniCssExtractPlugin({
          filename: '[name].[chunkhash:8].min.css',
          chunkFilename: '[id].[chunkhash:8].css',
        }),

        // Generate a manifest file which contains a mapping of all asset filenames
        // to their corresponding output file.
        new ManifestPlugin({
          fileName: 'asset-manifest.json',
        }),
      ];

    case 'development':
      return [
        new webpack.HotModuleReplacementPlugin(),
        new MiniCssExtractPlugin({
          filename: '[name].css',
        }),
      ];

    default:
      return [];
  }
};
