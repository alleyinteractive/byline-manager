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

  /**
   * Local state that contains hydrated (transformed profiles with different and
   * new attributes) profiles.
   *
   * The existing React components expect profiles to be formatted differently than
   * how the data is stored in post meta. Therefore, we will use this hydrated
   * profiles array as an intermediary state for existing React components,
   * while still ensuring that we save the data back to post meta as originally saved.
   *
   * In the long term we should consider removing this logic and just use the raw
   * post meta as the canonical format.
   */
  const [hydratedProfiles, setHydratedProfiles] = React.useState([]);

  /**
   * Hydrate profiles for use in this React app. The data is saved in another
   * format, so we will need to trandform the data back when saving to post meta.
   *
   * @param  {Array} items Array of profiles from post meta.
   * @return {Promise} Promise from an API request to get hydrated profiles.
   */
  const getHydrateProfiles = async (items) => {
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

  /**
   * Transforms hydrated profiles to a format that can be saved to post meta.
   *
   * @param {Array} items Hydrated profiles.
   * @return {Array} Array of profiles ready to be saved to post meta.
   */
  const transformHydratedProfiles = (items) => {
    if (0 >= items.length) {
      return [];
    }

    return items.map((value) => {
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

  /**
   * Save bylines to the Redux store.
   *
   * @param {Array} bylinesToSave Bylines to be saved to post meta. This should
   *                              be a hydrated array of profiles.
   */
  const saveBylines = (bylinesToSave) => {
    // Update the Redux store.
    onUpdate({
      profiles: [
        ...transformHydratedProfiles([...bylinesToSave]),
      ],
    });
  };

  /**
   * Add a single hydrated profile to an array of hydrated profiles.
   *
   * @param {Object} bylineToAdd Hydrated profile.
   */
  const addByline = (bylineToAdd) => {
    setHydratedProfiles(
      [
        ...hydratedProfiles,
        bylineToAdd,
      ]
    );
  };

  /**
   * Callback after profiles are sorted.
   *
   * @param {Integer} oldIndex Old index of the profile.
   * @param {Integer} newIndex New index of the profile.
   */
  const onSortEnd = ({ oldIndex, newIndex }) => {
    setHydratedProfiles(arrayMove([...hydratedProfiles], oldIndex, newIndex));
  };

  /**
   * Callback when a profile is removed.
   *
   * @param {String} id The hydrated profile ID to be removed.
   */
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

  /**
   * When the hydrated profiles are updated, transform and save the data to the
   * Redux store. This allows us to get data from post meta, hydrate the profiles,
   * perform modifications on the hydrated profiles, save back to post meta.
   */
  React.useEffect(() => {
    saveBylines([...hydratedProfiles]);
  }, [hydratedProfiles]);

  /**
   * On load hydrate the profiles.
   */
  React.useEffect(() => {
    setHydratedProfiles([]);

    async function hydrateProfiles() {
      setHydratedProfiles(await getHydrateProfiles(byline.profiles || []));
    }

    hydrateProfiles();
  }, []);

  return (
    <div style={{ width: '100%' }}>
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
