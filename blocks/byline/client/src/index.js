/* global wp */

/* eslint-disable */

/* eslint-disable react/prop-types */

/**
 * BLOCK: Bylines
 *
 * Register Bylines Block
 */

import edit from './edit';
import editFormat from './components/authorFormatType/EditAuthorFormat';

// Import CSS.
import './style.scss';

/**
 * WordPress dependencies
 */
const {
  blocks: {
    createBlock,
    registerBlockType,
  },
  data: {
    dispatch,
    select,
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

const blockName  = 'byline-manager/byline';
const formatName = 'byline-manager/author';

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
registerBlockType(blockName, {
  // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
  title: __('Byline Editor', 'byline'), // Block title.
  icon: 'id-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
  category: 'widgets', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
  keywords: [
    __('byline', 'byline'),
  ],
  attributes: {},
  supports: {
    multiple: false,
  },
  transforms: {
    from: [
      {
        type: 'block',
        blocks: ['core/paragraph'],
        transform: ({ content }) => {
          dispatch('core/editor').editPost({
            byline: { rendered: content },
          });
          return createBlock(blockName);
        },
      },
    ],
    to: [
      {
        type: 'block',
        blocks: ['core/paragraph'],
        transform: () => {
          const byline = select('core/editor')
            .getEditedPostAttribute('byline');
          const bylineRendered = byline.rendered || '';

          return createBlock('core/paragraph', { content: bylineRendered });
        },
      },
    ],
  },
  edit,
  save: () => null,
});

/**
 * Registers author format type.
 *
 * @param {string} name     Format name.
 * @param {Object} settings Format settings.
 *
 * @return {?WPFormat} The format, if it has been successfully registered;
 *                     otherwise `undefined`.
 */

registerFormatType(
  formatName, {
    title: __('Author', 'byline-manager'),
    tagName: 'a',
    attributes: {
      url: 'href',
      profileId: 'data-profile-id',
    },
    className: 'byline-manager-author',
    edit: editFormat,
  }
);
