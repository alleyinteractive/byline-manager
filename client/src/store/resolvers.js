// WordPress dependencies.
import { select } from '@wordpress/data';

// Internal dependencies.
import {
  actionReceiveHydratedProfiles,
  actionHydrateProfiles,
} from './actions';

/**
 * Resolver to return profiles hydrated.
 *
 * @param {string} storeKey Store key.
 * @param {string} metaKey Meta key.
 * @return {Object} Generator object.
 */
function* resolveProfiles(storeKey, metaKey) {
  const {
    profilesHydrated,
    byline,
  } = select(storeKey);

  // If hydration has already happened, return profiles already in state.
  if (profilesHydrated) {
    return actionReceiveHydratedProfiles(byline.profiles);
  }

  // Fetch profile data from hydration endpoint and merge into state.
  const meta = select('core/editor').getEditedPostAttribute('meta');

  const metaBylines = meta[metaKey] || {};

  const hydratedProfiles = yield actionHydrateProfiles(metaBylines.profiles || []);

  return actionReceiveHydratedProfiles(hydratedProfiles);
}

export default resolveProfiles;
