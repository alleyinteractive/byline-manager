// Internal dependencies.
import { HYDRATE_ACTION_TYPES } from '../actions-types';

export const actionReceiveHydratedProfiles = (profiles) => ({
  type: HYDRATE_ACTION_TYPES.RECEIVE_HYDRATED_PROFILES,
  payload: profiles,
});

export const actionHydrateProfiles = (metaBylines) => ({
  type: HYDRATE_ACTION_TYPES.HYDRATE_PROFILES,
  payload: metaBylines,
});
