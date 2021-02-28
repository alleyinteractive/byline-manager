import getHydrateProfiles from './getHydrateProfiles';

const {
  data: {
    select,
  },
} = wp;

export const hydrateActions = {
  HYDRATE_PROFILES: 'byline-manager/hydrate/HYDRATE_PROFILES',
  RECEIVE_HYDRATED_PROFILES: 'byline-manager/hydrate/RECEIVE_HYDRATED_PROFILES',
};

const reducer = (state = {}, action = {}) => {
  const {
    type,
    payload,
  } = action;

  switch (type) {
    case hydrateActions.RECEIVE_HYDRATED_PROFILES:
      return {
        byline: {
          profiles: state.byline.profiles.concat(payload),
        },
        profilesHydrated: true,
      };

    default:
      return state;
  }
};

export const actionHydrateProfiles = (metaBylines) => ({
  type: hydrateActions.HYDRATE_PROFILES,
  payload: metaBylines,
});

export const actionReceiveHydratedProfiles = (profiles) => ({
  type: hydrateActions.RECEIVE_HYDRATED_PROFILES,
  payload: profiles,
});

export const getProfiles = (state) => (
  state.byline.profiles || []
);

export function* resolveProfiles() {
  const {
    profilesHydrated,
    byline,
  } = select('byline-manager');

  // If hydration has already happened, return profiles already in state.
  if (profilesHydrated) {
    return actionReceiveHydratedProfiles(byline.profiles);
  }

  // Fetch profile data from hydration endpoint and merge into state.
  const metaBylines = select('core/editor')
    .getEditedPostAttribute('meta').byline || {};
  const hydratedProfiles = yield actionHydrateProfiles(metaBylines.profiles);
  return actionReceiveHydratedProfiles(hydratedProfiles);
}

export const hydrateProfilesControl = async (action) => (
  getHydrateProfiles(action.payload)
);

export default reducer;
