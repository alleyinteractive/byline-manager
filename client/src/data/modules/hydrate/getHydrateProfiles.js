let profilesHydrated = false; // eslint-disable-line import/no-mutable-exports

/**
 * Hydrate profiles for use in this React app. The data is saved in another
 * format, so we will need to trandform the data back when saving to post meta.
 *
 * @param  {Array} items Array of profiles from post meta.
 * @return {Promise} Promise from an API request to get hydrated profiles.
 */
const getHydrateProfiles = async (items = []) => {
  if (0 >= items.length) {
    return Promise.resolve([]);
  }

  return wp.apiFetch({
    path: '/byline-manager/v1/hydrateProfiles/',
    method: 'POST',
    data: {
      profiles: items,
    },
  })
    .then((value) => {
      profilesHydrated = true;
      return value;
    })
    .catch(() => []);
};

export default getHydrateProfiles;
export { profilesHydrated };
