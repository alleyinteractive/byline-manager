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
        placeholder={
          bylineRendered ?
            '' :
            __(
              'Start writing, or select an author from the toolbar.',
              'byline-manager'
            )
        }
      />
    </Fragment>
  );
};

BylineEdit.propTypes = {
  bylineRendered: PropTypes.string.isRequired,
  isSelected: PropTypes.func.isRequired,
  onChange: PropTypes.func.isRequired,
};

export default compose(
  withSelect((select) => ({
    bylineRendered:
      select('core/editor').getEditedPostAttribute('byline').rendered,
  })),
  withDispatch((dispatch) => ({
    onChange: (nextByline) => {
      dispatch('core/editor').editPost({
        byline: { rendered: nextByline },
      });
    },
  })),
)(BylineEdit);

