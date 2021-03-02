/* globals React */
import PropTypes from 'prop-types';
import createSaveByline from './createSaveByline';
import BylineAutocomplete from './bylineAutocomplete';
import BylineFreeform from './bylineFreeform';
import BylineList from './bylineList';

const {
  compose: {
    compose,
  },
  data: {
    withSelect,
    withDispatch,
  },
} = wp;

const BylineSlot = (props) => {
  const {
    profiles,
    saveByline,
    addProfile,
    removeProfile,
    reorderProfile,
  } = props;

  React.useEffect(() => {
    saveByline(profiles);
  });

  return (
    <div style={{ width: '100%' }}>
      <BylineAutocomplete
        profiles={profiles}
        onUpdate={addProfile}
      />
      <BylineFreeform
        onUpdate={addProfile}
      />
      <BylineList
        profiles={profiles}
        onSortEnd={reorderProfile}
        lockAxis="y"
        helperClass="byline-list-item"
        removeItem={removeProfile}
      />
    </div>
  );
};

BylineSlot.propTypes = {
  profiles: PropTypes.arrayOf(
    PropTypes.shape({
      byline_id: PropTypes.number,
      id: PropTypes.number,
      image: PropTypes.string,
      name: PropTypes.string,
    })
  ).isRequired,
  saveByline: PropTypes.func.isRequired,
  addProfile: PropTypes.func.isRequired,
  removeProfile: PropTypes.func.isRequired,
  reorderProfile: PropTypes.func.isRequired,
};

export default compose([
  withSelect((select) => {
    const profiles = select('byline-manager').getProfiles();

    return { profiles };
  }),
  withDispatch((dispatch) => {
    const {
      actionAddProfile,
      actionRemoveProfile,
      actionReorderProfile,
    } = dispatch('byline-manager');

    return {
      saveByline: createSaveByline(dispatch),
      addProfile: actionAddProfile,
      removeProfile: actionRemoveProfile,
      reorderProfile: actionReorderProfile,
    };
  }),
])(BylineSlot);
