import hydrateReducer, {
  actionHydrateProfiles,
  actionReceiveHydratedProfiles,
  getProfiles,
  resolveProfiles,
  hydrateControls,
} from './modules/hydrate';

const {
  data: {
    combineReducers,
    createReduxStore,
    register,
  },
} = wp;

const actions = {
    actionHydrateProfiles,
    actionReceiveHydratedProfiles,
};

const store = createReduxStore( 'byline-manager', {
    reducer: combineReducers({
      byline: hydrateReducer,
    }),

    actions,

    selectors: {
        getProfiles,
    },

    controls: {
      ...hydrateControls,
    },

    resolvers: {
      getProfiles: resolveProfiles,
    },
} );

register(store);