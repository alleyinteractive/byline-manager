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
  components: {
    PanelBody,
  },
  compose: {
    compose,
  },
  editor: {
    InspectorControls,
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
    isSelected,
  } = props;

  // @memberof BylineEdit
  const inspectorControls = (
    <InspectorControls key="inspector">
      <PanelBody title={__('Byline settings', 'dow-jones')}>
        {'byline inspector settings'}
      </PanelBody>
    </InspectorControls>);

  return (
    <Fragment key="itemFragment">
      {isSelected && inspectorControls}
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
  isSelected: PropTypes.func.isRequired,
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

