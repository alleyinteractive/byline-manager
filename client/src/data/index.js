import hydrateReducer, {
  actionHydrateProfiles,
  actionReceiveHydratedProfiles,
  getProfiles,
  resolveProfiles,
  hydrateProfilesControl,
  hydrateActions,
} from './modules/hydrate';
import modifyReducer, {
  actionAddProfile,
  actionRemoveProfile,
  actionReorderProfile,
  modifyActions,
} from './modules/modify';

const {
  data: {
    registerStore,
  },
} = wp;

const actions = {
  actionHydrateProfiles,
  actionReceiveHydratedProfiles,
  actionAddProfile,
  actionRemoveProfile,
  actionReorderProfile,
};

const defaultState = {
  byline: {
    profiles: [],
  },
  profilesHydrated: false,
};

const store = {
  reducer: (state = defaultState, action = {}) => {
    switch (true) {
      case Object.values(hydrateActions).includes(action.type):
        return hydrateReducer(state, action);

      case Object.values(modifyActions).includes(action.type):
        return {
          byline: {
            profiles: modifyReducer(state.byline.profiles, action),
          },
        };

      default:
        return state;
    }
  },

  actions,

  selectors: {
    getProfiles,
  },

  controls: {
    [hydrateActions.HYDRATE_PROFILES]: hydrateProfilesControl,
  },

  resolvers: {
    getProfiles: resolveProfiles,
  },
};

registerStore('byline-manager', store);
