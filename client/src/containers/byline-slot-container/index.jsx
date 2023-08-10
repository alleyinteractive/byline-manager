// External dependencies.
import React from 'react';
import PropTypes from 'prop-types';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

// Internal dependencies.
import setBylineMeta from '../../utils/set-byline';
import BylineSlotWrapper from '../../components/byline-slot-wrapper';

const BylineSlotContainer = ({
  store,
  metakey,
}) => {
  const profiles = useSelect(
    (select) => select(store).getProfiles()
  );

  const {
    actionAddProfile: addProfile,
    actionRemoveProfile: removeProfile,
    actionReorderProfile: reorderProfile,
  } = useDispatch(store);

  const saveByline = setBylineMeta(dispatch, metakey);

  /**
   * Save ALL bylines to meta in the expected schema.
   *
   * This is a more efficient way to save bylines to theh post meta with the right schema.
   * Since the redux store schema and the meta schema are different.
   */
  useEffect(() => {
    if (null !== profiles) {
      saveByline(profiles);
    }
  }, [profiles]);

  return (
    <BylineSlotWrapper
      profiles={profiles}
      addProfile={addProfile}
      removeProfile={removeProfile}
      reorderProfile={reorderProfile}
    />
  );
};

BylineSlotContainer.defaultProps = {
  metakey: 'byline',
};

BylineSlotContainer.propTypes = {
  metakey: PropTypes.string,
  store: PropTypes.string.isRequired,
};

export default BylineSlotContainer;
