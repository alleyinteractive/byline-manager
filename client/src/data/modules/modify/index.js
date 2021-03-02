import arrayMove from 'array-move';

export const modifyActions = {
  ADD_PROFILE: 'byline-manager/modify/ADD_PROFILE',
  REMOVE_PROFILE: 'byline-manager/modify/REMOVE_PROFILE',
  REORDER_PROFILE: 'byline-manager/modify/REORDER_PROFILE',
};

const reducer = (state = [], action = {}) => {
  const {
    type,
    payload,
  } = action;

  /* eslint-disable no-case-declarations */
  switch (type) {
    case modifyActions.ADD_PROFILE:
      return [
        ...state,
        action.payload,
      ];

    case modifyActions.REMOVE_PROFILE:
      const index = state.findIndex(
        (item) => item.id === payload
      );

      if (0 <= index) {
        state.filter((item) => item.id !== payload);
      }

      return state;

    case modifyActions.REORDER_PROFILE:
      const { oldIndex, newIndex } = payload;
      return arrayMove(
        state,
        oldIndex,
        newIndex
      );

    default:
      return state;
  }
  /* eslint-enable */
};

/**
 * Creating action to add a single hydrated profile to an array of
 * hydrated profiles.
 *
 * @param {Object} bylineToAdd Hydrated profile.
 */
export const actionAddProfile = (bylineToAdd) => ({
  type: modifyActions.ADD_PROFILE,
  payload: bylineToAdd,
});

/**
 * Create action to remove a profile by ID from the current array of profiles.
 *
 * @param {String} id The hydrated profile ID to be removed.
 */
export const actionRemoveProfile = (id) => ({
  type: modifyActions.REMOVE_PROFILE,
  payload: id,
});

/**
 * Create action to reorder profiles currently selected.
 *
 * @param {object} indices old and new index for moved item.
 */
export const actionReorderProfile = (indices) => ({
  type: modifyActions.REORDER_PROFILE,
  payload: indices,
});

export default reducer;
