// Internal dependencies.
import { MODIFY_ACTION_TYPES } from '../actions-types';

/**
 * Create action to add a single hydrated profile to an array of
 * hydrated profiles.
 *
 * @param {Object} bylineToAdd Hydrated profile.
 */
export const actionAddProfile = (bylineToAdd) => ({
  type: MODIFY_ACTION_TYPES.ADD_PROFILE,
  payload: bylineToAdd,
});

/**
 * Create action to remove a profile by ID from the current array of profiles.
 *
 * @param {String} id The hydrated profile ID to be removed.
 */
export const actionRemoveProfile = (id) => ({
  type: MODIFY_ACTION_TYPES.REMOVE_PROFILE,
  payload: id,
});

/**
 * Create action to reorder profiles currently selected.
 *
 * @param {Object} indices old and new index for moved item.
 */
export const actionReorderProfile = (indices) => ({
  type: MODIFY_ACTION_TYPES.REORDER_PROFILE,
  payload: indices,
});
