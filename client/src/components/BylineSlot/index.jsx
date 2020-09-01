import PropTypes from 'prop-types';
import React from 'react';
import BylineProfiles from '../BylineMetaBox/BylineProfiles';
/* eslint-disable */

debugger;

const {
  compose: {
    compose,
  },
  data: {
    withSelect,
    withDispatch,
  },
} = wp;

const BylineSlot = ({
  metaKey,
  metaValue,
  onUpdate,
}) => (
  <BylineProfiles
    onUpdate={(value) => onUpdate(metaKey, value)}
	profiles={window.bylineData.bylineMetaBox.profiles || {}}
	metaValue={metaValue}
  />
);

BylineSlot.propTypes = {
  metaKey: PropTypes.string.isRequired,
  metaValue: PropTypes.string.isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default compose([
  withSelect((select, props) => {
	  debugger;
    const { metaKey } = props;

    // Set up our selectors to get data from the store.
    const editor = select('core/editor');

	console.log(editor.getEditedPostAttribute('meta'));
    // Extract the meta key as primaryTerm from edited post meta.
    const {
      [metaKey]: metaValue,
	} = editor.getEditedPostAttribute('meta');

	debugger;
	console.log(metaValue.profiles);
	console.log(window.bylineData.bylineMetaBox.profiles);
    return {
      metaValue,
    };
  }),
  withDispatch((dispatch) => ({
    onUpdate: (metaKey, metaValue) => {
      dispatch('core/editor').editPost({
        meta: {
          [metaKey]: metaValue,
        },
      });
    },
  })),
])(BylineSlot);
