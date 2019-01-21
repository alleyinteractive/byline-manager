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
    AlignmentToolbar,
    BlockControls,
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
    attributes: {
      align,
    },
    byline,
    onChange,
    isSelected,
    setAttributes,
  } = props;

  // @memberof BylineEdit
  const blockControls = (
    <BlockControls>
      <AlignmentToolbar
        value={align}
        onChange={(nextAlign) => {
          setAttributes({ align: nextAlign });
        }}
      />
    </BlockControls>
  );

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
      {blockControls}
      <RichText
        identifier="byline"
        formattingControls={['bold', 'italic', 'strikethrough']}
        tagName="p"
        value={byline}
        onChange={onChange}
        style={{
          textAlign: align,
        }}
        aria-label={__('Byline block', 'byline-manager')}
        placeholder={
          byline ?
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
  attributes: PropTypes.object.isRequired,
  setAttributes: PropTypes.func.isRequired,
  byline: PropTypes.string.isRequired,
  isSelected: PropTypes.func.isRequired,
  onChange: PropTypes.func.isRequired,
};

export default compose(
  withSelect((select) => ({
    byline: select('core/editor').getEditedPostAttribute('byline_rendered'),
  })),
  withDispatch((dispatch) => ({
    onChange: (nextByline) => {
      dispatch('core/editor').editPost({
        byline_rendered: nextByline,
      });
    },
  })),
)(BylineEdit);

