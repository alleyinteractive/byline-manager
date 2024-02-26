/* eslint-disable quote-props */
module.exports = {
  extends: ['@alleyinteractive/stylelint-config'],
  plugins: [
    'stylelint-order', // Plugin for enforcing alpha ordering of properties
  ],
  rules: {
    'at-rule-empty-line-before': ['always', {
      except: [
        'blockless-after-same-name-blockless',
        'first-nested',
      ],
      ignore: ['after-comment'],
    }],
    'selector-max-id': 1
  },
};
