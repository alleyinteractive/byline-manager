// WordPress dependencies.
import apiFetch from '@wordpress/api-fetch';

/**
 * Hydrate profiles for use in this React app. The data is saved in another
 * format, so we will need to transform the data back when saving to post meta.
 *
 * @param  {Array} items Array of profiles from post meta.
 * @return {Promise} Promise from an API request to get hydrated profiles.
 */
const getHydrateProfiles = async (items = []) => apiFetch({
  path: '/byline-manager/v1/hydrateProfiles/',
  method: 'POST',
  data: {
    profiles: items,
  },
}).catch(() => []);

export default getHydrateProfiles;
