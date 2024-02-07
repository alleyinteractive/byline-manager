const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const StatsPlugin = require('webpack-stats-plugin').StatsWriterPlugin;
const DependencyExtractionWebpackPlugin =
  require('@wordpress/dependency-extraction-webpack-plugin');
const ESLintPlugin = require('eslint-webpack-plugin');
const StylelintPlugin = require('stylelint-webpack-plugin');
const createWriteWpAssetManifest = require('./wpAssets');

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
        new ESLintPlugin(),
        new StylelintPlugin({
          configFile: path.join(paths.config, 'stylelint.config.js'),
        }),
        new MiniCssExtractPlugin({
          filename: '[name].[chunkhash:8].min.css',
          chunkFilename: '[id].[chunkhash:8].css',
        }),

        // This creates our assetMap.json file to get build hashes for cache busting.
        new StatsPlugin({
          transform: createWriteWpAssetManifest,
          fields: ['assetsByChunkName', 'hash'],
          filename: 'assetMap.json',
        }),
      ];

    case 'development':
      return [
        // This maps references to @wordpress/{package-name} to the wp object.
        new DependencyExtractionWebpackPlugin(),
        new MiniCssExtractPlugin({
          filename: '[name].css',
        }),
        new ESLintPlugin(),
        new StylelintPlugin({
          configFile: path.join(paths.config, 'stylelint.config.js'),
        }),
        // This creates our assetMap.json file to get build hashes for cache busting.
        new StatsPlugin({
          transform: createWriteWpAssetManifest,
          fields: ['assetsByChunkName', 'hash'],
          filename: 'assetMap.json',
        }),
      ];

    default:
      return [];
  }
};
