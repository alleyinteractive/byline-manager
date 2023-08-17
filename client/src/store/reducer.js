// Internal dependencies.
import {
  HYDRATE_ACTION_TYPES,
  MODIFY_ACTION_TYPES,
} from './actions-types';
import hydrateReducer from './reducers/hydrate-reducer';
import modifyReducer from './reducers/modify-reducer';

/**
 * Default state.
 */
const DEFAULT_STATE = {
  byline: {
    profiles: [],
  },
  profilesHydrated: false,
};

const reducer = (state = DEFAULT_STATE, action = {}) => {
  switch (true) {
    case Object.values(HYDRATE_ACTION_TYPES).includes(action.type):
      return hydrateReducer(state, action);

    case Object.values(MODIFY_ACTION_TYPES).includes(action.type):
      return {
        byline: {
          profiles: modifyReducer(state.byline.profiles, action),
        },
        profilesHydrated: state.profilesHydrated,
      };

    default:
      return state;
  }
};

export default reducer;
