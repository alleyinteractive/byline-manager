// External dependencies.
import PropTypes from 'prop-types';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useCallback } from '@wordpress/element';

// Internal dependencies.
import setBylineMeta from '../../utils/set-byline';
import BylineSlotWrapper from '../../components/byline-slot-wrapper';

function BylineSlotContainer({
  metaKey,
  store,
}) {
  const profiles = useSelect((select) => select(store).getProfiles(), []);

  const {
    actionAddProfile: addProfile,
    actionRemoveProfile: removeProfile,
    actionReorderProfile: reorderProfile,
  } = useDispatch(store);

  // eslint-disable-next-line react-hooks/exhaustive-deps
  const saveByline = useCallback(setBylineMeta(dispatch, metaKey), [metaKey]);

  /**
   * Save ALL bylines to the post meta in the expected schema.
   *
   * This is an efficient way to save bylines to the post meta in the "right" schema.
   * The redux store schema and the meta schema are different.
   */
  useEffect(() => {
    if (profiles !== null) {
      saveByline(profiles);
    }
  }, [profiles, saveByline]);

  return (
    <BylineSlotWrapper
      profiles={profiles}
      addProfile={addProfile}
      removeProfile={removeProfile}
      reorderProfile={reorderProfile}
    />
  );
}

BylineSlotContainer.defaultProps = {
  metaKey: 'byline',
};

BylineSlotContainer.propTypes = {
  metaKey: PropTypes.string,
  store: PropTypes.string.isRequired,
};

export default BylineSlotContainer;
