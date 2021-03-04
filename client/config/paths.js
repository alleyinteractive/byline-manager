const path = require('path');

module.exports = {
  clientRoot: path.join(__dirname, '../'),
  appRoot: path.join(__dirname, '../src'),
  buildRoot: path.join(__dirname, '../build'),
  config: __dirname,
  globalStyles: path.join(__dirname, '../src/styles'),
  assetsRoot: path.join(__dirname, '../src/assets'),
};
