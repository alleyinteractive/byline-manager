// Internal dependencies.
import { MODIFY_ACTION_TYPES } from '../actions-types';
import removeIndex from '../../utils/remove-index';
import reorderIndex from '../../utils/reorder-index';

const reducer = (state = [], action = {}) => {
  const {
    type,
    payload,
  } = action;

  switch (type) {
    case MODIFY_ACTION_TYPES.ADD_PROFILE: {
      return [
        ...state,
        action.payload,
      ];
    }

    case MODIFY_ACTION_TYPES.REMOVE_PROFILE: {
      return removeIndex(state, payload);
    }

    case MODIFY_ACTION_TYPES.REORDER_PROFILE: {
      const { oldIndex, newIndex } = payload;
      return reorderIndex(state, oldIndex, newIndex);
    }

    default: {
      return state;
    }
  }
};

export default reducer;
