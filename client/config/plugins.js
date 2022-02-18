const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

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
        // This maps references to @wordpress/{package-name} to the wp object.
        new DependencyExtractionWebpackPlugin(),
        new webpack.NoEmitOnErrorsPlugin(),
        new StylelintPlugin({
          configFile: path.join(paths.config, 'stylelint.config.js'),
        }),
        new MiniCssExtractPlugin({
          filename: '[name].[chunkhash:8].min.css',
          chunkFilename: '[id].[chunkhash:8].css',
        }),

        // Generate a manifest file which contains a mapping of all asset filenames
        // to their corresponding output file.
        new WebpackManifestPlugin({
          publicPath: '',
          fileName: 'asset-manifest.json',
        }),
      ];

    case 'development':
      return [
        // This maps references to @wordpress/{package-name} to the wp object.
        new DependencyExtractionWebpackPlugin(),
        new MiniCssExtractPlugin({
          filename: '[name].css',
        }),
        new StylelintPlugin({
          configFile: path.join(paths.config, 'stylelint.config.js'),
        }),
        new WebpackManifestPlugin({
          publicPath: '',
          fileName: 'asset-manifest.json',
        }),

      ];

    default:
      return [];
  }
};
