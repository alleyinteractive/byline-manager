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
      namedModules: true,
      noEmitOnErrors: true,
    },
    output: getOutput(mode),
    plugins: getPlugins(mode),
    resolve: {
      extensions: ['.js', '.jsx'],
      modules: ['node_modules'],
    },
  };
};
