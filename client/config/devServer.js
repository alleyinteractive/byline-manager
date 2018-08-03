const fs = require('fs');
const os = require('os');
const path = require('path');

/**
 * Returns `devServer` configuration based on provided mode.
 * @param {Object} env - Information about the webpack environment.
 * @param {String} mode - The mode that is being used.
 * @returns {Object} - A `devServer` config object for the provided mode.
 */
module.exports = function getDevServer(env, mode) {
  const certPath = env.certPath || path.join(
    os.homedir(),
    'broadway/config/nginx-config'
  );

  switch (mode) {
    case 'development':
      return {
        hot: true,
        quiet: false,
        noInfo: false,
        headers: { 'Access-Control-Allow-Origin': '*' },
        stats: { colors: true },
        https: env.http ? false : {
          cert: fs.readFileSync(
            path.join(certPath, 'server.crt'),
            'utf8'
          ),
          key: fs.readFileSync(
            path.join(certPath, 'server.key'),
            'utf8'
          ),
        },
      };

    default:
      return {};
  }
};
