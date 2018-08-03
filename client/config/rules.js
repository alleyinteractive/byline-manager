const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const paths = require('./paths');

const exclude = [
  /node_modules/,
  /\.min\.js$/,
];

/**
 * Returns `rules` loader configuration based on provided mode.
 * @param {String} mode - The mode that is being used.
 * @returns {Array} - A `rules` config array for the specified mode.
 */
module.exports = function getLoaders(mode) {
  const isProd = 'production' === mode;
  return [
    {
      enforce: 'pre',
      test: /\.jsx?$/,
      exclude,
      use: 'eslint-loader',
    },
    {
      test: /\.jsx?$/,
      exclude,
      use: 'babel-loader',
    },
    {
      test: /\.scss$/,
      exclude: /node_modules/,
      use: [
        isProd ? MiniCssExtractPlugin.loader : 'style-loader',
        {
          loader: 'css-loader',
          options: {
            url: false,
            minimize: isProd,
            importLoaders: 1,
            sourceMap: ! isProd,
          },
        },
        {
          loader: 'postcss-loader',
          options: {
            sourceMap: ! isProd,
            config: {
              path: path.join(paths.config, 'postcss.config.js'),
            },
          },
        },
        {
          loader: 'sass-loader',
          options: {
            sourceMap: true,
          },
        },
      ],
    },
  ];
};
