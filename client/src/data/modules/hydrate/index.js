import getHydrateProfiles from './getHydrateProfiles';
import transformHydratedProfiles from './transformHydratedProfiles';

const {
  data: {
    select,
  },
} = wp;

const defaultState = {
  byline: {
    profiles: []
  },
  profilesHydrated: false,
};

const HYDRATE_PROFILES = 'byline-manager/hydrate/HYDRATE_PROFILES';
const RECEIVE_HYDRATED_PROFILES =
  'byline-manager/hydrate/RECEIVE_HYDRATED_PROFILES';

const reducer = (state = defaultState, action = {}) => {
  switch (action.type) {
    case RECEIVE_HYDRATED_PROFILES:
      return {
        profiles: state.profiles.concat(action.payload),
        profilesHydrated: true,
      };

    default:
      return state;
  }
};

const actionHydrateProfiles = (metaBylines) => ({
  type: HYDRATE_PROFILES,
  payload: metaBylines,
});

const actionReceiveHydratedProfiles = (profiles) => ({
  type: RECEIVE_HYDRATED_PROFILES,
  payload: profiles,
});

const getProfiles = (state) => (
  state.byline.profiles || []
);

function *resolveProfiles() {
  const metaBylines = select('core/editor').getEditedPostAttribute('meta').byline
    || {};
  const hydratedBylines = yield actionHydrateProfiles(metaBylines);
  return actionReceiveHydratedProfiles(
    transformHydratedProfiles(hydratedBylines)
  );
};

const hydrateControls = {
  [HYDRATE_PROFILES]: async (action) => {
    await getHydrateProfiles(action.payload);
  },
}

export {
  getProfiles,
  resolveProfiles,
  hydrateControls,
};

export default reducer;
