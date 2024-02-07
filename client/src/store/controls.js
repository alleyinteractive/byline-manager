// Internal dependencies.
import { HYDRATE_ACTION_TYPES } from './actions-types';
import getHydrateProfiles from '../utils/get-hydrate-profiles';

export default {
  [HYDRATE_ACTION_TYPES.HYDRATE_PROFILES]: async (action) => getHydrateProfiles(action.payload),
};
