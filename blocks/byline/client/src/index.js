/* global wp */

/* eslint-disable */

/* eslint-disable react/prop-types */

/**
 * BLOCK: Bylines
 *
 * Register Bylines Block
 */

import edit from './edit';
import editFormat from './editFormat';

// Import CSS.
import './style.scss';

/**
 * WordPress dependencies
 */
const {
  blocks: {
    getPhrasingContentSchema,
    registerBlockType,
  },
  editor: {
    RichText,
  },
  i18n: {
    __,
    setLocaleData,
  },
  richText: {
    registerFormatType,
  },
} = wp;

// Register the textdomain.
setLocaleData({ '': {} }, 'byline');

/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType('dj/byline', {
  // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
  title: __('Byline Editor', 'byline'), // Block title.
  icon: 'id', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
  category: 'widgets', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
  keywords: [
    __('byline', 'byline'),
  ],
  attributes: {
    align: {
      type: 'string',
    },
  },
  supports: {
    className: false,
  },
  transforms: {
    from: [
      {
        type: 'raw',
        priority: 20,
        selector: 'p',
        schema: {
          p: {
            children: getPhrasingContentSchema(),
          },
        },
      },
    ],
    to: [
      {
        type: 'block',
        blocks: ['core/paragraph'],
        transform: ({ content }) => createBlock(
          'core/paragraph', {
            content,
          }
        ),
      },
    ],
  },
  edit,
  save: () => null,
});

// @todo Add an author format button.
