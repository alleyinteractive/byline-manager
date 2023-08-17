// Internal dependencies.
import { HYDRATE_ACTION_TYPES } from '../actions-types';

const reducer = (state = {}, action = {}) => {
  const {
    type,
    payload,
  } = action;

  switch (type) {
    case HYDRATE_ACTION_TYPES.RECEIVE_HYDRATED_PROFILES:
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

export default reducer;
