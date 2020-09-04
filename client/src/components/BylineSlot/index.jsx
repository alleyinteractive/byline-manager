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

  const getHydrateProfiles = (items) => {
    if (0 >= items.length) {
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

  const transformHydratedProfiles = (profiles) => {
    if (0 >= profiles.length) {
      return [];
    }

    return profiles.map((value) => {
      // Profile type.
      if (value.byline_id && 'number' === typeof value.id) {
        return {
          type: 'byline_id',
          atts: {
            term_id: value.byline_id,
            post_id: value.id,
          },
        };
      }

      return {
        type: 'text',
        atts: {
          text: value.name,
        },
      };
    });
  };

  const saveBylines = (bylinesToSave) => {
    // Update the Redux store.
    onUpdate({
      profiles: [
        ...transformHydratedProfiles([...bylinesToSave]),
      ],
    });
  };

  const addByline = (bylineToAdd) => {
    setHydratedProfiles(
      [
        ...hydratedProfiles,
        bylineToAdd,
      ]
    );
  };

  const onSortEnd = ({ oldIndex, newIndex }) => {
    setHydratedProfiles(arrayMove([...hydratedProfiles], oldIndex, newIndex));
  };

  const removeItem = (id) => {
    const index = hydratedProfiles.findIndex((item) => item.id === id);

    if (0 <= index) {
      setHydratedProfiles(
        [
          ...hydratedProfiles.slice(0, index),
          ...hydratedProfiles.slice(index + 1),
        ]
      );
    }
  };

  React.useEffect(() => {
    saveBylines([...hydratedProfiles]);
  }, [hydratedProfiles]);

  React.useEffect(() => {
    setHydratedProfiles([]);

    async function hydrateProfiles() {
      setHydratedProfiles(await getHydrateProfiles(byline.profiles || []));
    }

    hydrateProfiles();
  }, []);

  return (
    <div>
      <BylineAutocomplete
        byline={{
          profiles: hydratedProfiles,
        }}
        onUpdate={setHydratedProfiles}
      />
      <BylineFreeform
        onUpdate={addByline}
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
    onUpdate: (metaValue) => {
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
          byline: metaValue,
        },
      });
    },
  })),
])(BylineSlot);
