/* globals React */
import PropTypes from 'prop-types';
import { arrayMove } from 'react-sortable-hoc';
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
    byline,
    onUpdate,
  } = props;

  const [hydratedProfiles, setHydratedProfiles] = React.useState([]);

  const onSortEnd = ({ oldIndex, newIndex }) => {
    onUpdate('byline', {
      profiles: arrayMove([...byline.profiles], oldIndex, newIndex),
    });
  };

  const removeItem = (id) => {
    const { profiles } = byline;
    const index = hydratedProfiles.findIndex((item) => item.id === id);

    if (0 <= index) {
      onUpdate('byline', {
        profiles: [
          ...profiles.slice(0, index),
          ...profiles.slice(index + 1),
        ],
      });
    }
  };

  const getHydrateProfiles = (items) => {
    if (0 > items.length) {
      return [];
    }

    return wp.apiFetch({
      path: '/byline-manager/v1/hydrateProfiles/',
      method: 'POST',
      data: {
        profiles: items,
      },
    }).then((value) => value)
      .catch(() => []);
  };

  React.useEffect(() => {
    setHydratedProfiles([]);

    async function hydrateProfiles() {
      setHydratedProfiles(await getHydrateProfiles(byline.profiles || []));
    }

    hydrateProfiles();
  }, [byline]);

  return (
    <div>
      <BylineAutocomplete
        byline={byline}
        onUpdate={onUpdate}
      />
      <BylineFreeform
        byline={byline}
        onUpdate={onUpdate}
      />
      <BylineList
        profiles={hydratedProfiles}
        onSortEnd={onSortEnd}
        lockAxis="y"
        helperClass="byline-list-item"
        removeItem={removeItem}
      />
    </div>
  );
};

BylineSlot.propTypes = {
  byline: PropTypes.shape({
    profiles: PropTypes.array,
  }).isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default compose([
  withSelect((select) => {
    const editor = select('core/editor');
    const {
      getEditedPostAttribute,
    } = editor;

    return {
      byline: getEditedPostAttribute('meta').byline || {},
    };
  }),
  withDispatch((dispatch) => ({
    onUpdate: (metaKey, metaValue) => {
      const termBylines = metaValue.profiles.filter(
        (value) => 'undefined' !== typeof value.type &&
        'byline_id' === value.type
      );

      if (0 < termBylines.length) {
        dispatch('core/editor').editPost({
          byline: [...termBylines.map((item) => item.atts.term_id)],
        });
      } else {
        dispatch('core/editor').editPost({
          byline: [],
        });
      }

      dispatch('core/editor').editPost({
        meta: {
          [metaKey]: metaValue,
        },
      });
    },
  })),
])(BylineSlot);
