/* global wp */

/**
 * BLOCK EDIT: Byline Block Plugin
 *
 * Define Byline block edit
 */

import PropTypes from 'prop-types';

/**
 * WP dependencies
 */
const {
  compose: {
    compose,
  },
  editor: {
    RichText,
  },
  element: {
    Fragment,
  },
  data: {
    withDispatch,
    withSelect,
  },
  i18n: {
    __,
  },
} = wp;

const BylineEdit = (props) => {
  const {
    className,
    bylineRendered,
    onChange,
  } = props;

  return (
    <Fragment key="itemFragment">
      <RichText
        identifier="byline"
        formattingControls={['bold', 'italic', 'strikethrough']}
        tagName="p"
        value={bylineRendered}
        onChange={onChange}
        aria-label={__('Byline block', 'byline-manager')}
        placeholder={__('Start writing, or insert an author from the toolbar.',
          'byline-manager')}
        className={className}
      />
    </Fragment>
  );
};

BylineEdit.propTypes = {
  className: PropTypes.string.isRequired,
  bylineRendered: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
};

export default compose(
  withSelect((select) => {
    const byline = select('core/editor').getEditedPostAttribute('byline');
    const bylineRendered = byline ? byline.rendered :
      __('By ', 'byline-manager');
    return ({ bylineRendered });
  }),
  withDispatch((dispatch) => ({
    onChange: (nextByline) => {
      dispatch('core/editor').editPost({
        byline: { rendered: nextByline },
      });
    },
  })),
)(BylineEdit);

