/**
 * Add a single hydrated profile to an array of hydrated profiles.
 *
 * @param {Object} bylineToAdd Hydrated profile.
 */
const actionAddProfile = (bylineToAdd) => {
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
const actionReorderProfile = ({ oldIndex, newIndex }) => {
  setHydratedProfiles(arrayMove([...hydratedProfiles], oldIndex, newIndex));
};

/**
 * Callback when a profile is removed.
 *
 * @param {String} id The hydrated profile ID to be removed.
 */
const actionRemoveProfile = (id) => {
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
