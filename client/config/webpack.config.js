const CSSMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const getEntry = require('./entry');
const getRules = require('./rules');
const getOutput = require('./output');
const getPlugins = require('./plugins');

module.exports = (env, argv) => {
  const { mode } = argv;
  return {
    entry: getEntry(mode),
    module: {
      rules: getRules(mode),
    },
    optimization: {
      moduleIds: 'named',
      noEmitOnErrors: true,
      minimizer: 'production' === mode ? [
        new TerserJSPlugin({}),
        new CSSMinimizerPlugin(),
      ] : [],
    },
    output: getOutput(mode),
    plugins: getPlugins(mode),
    resolve: {
      extensions: ['.js', '.jsx'],
      modules: ['node_modules'],
    },
  };
};
