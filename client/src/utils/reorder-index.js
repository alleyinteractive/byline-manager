// External dependencies.
import { arrayMoveImmutable as arrayMove } from 'array-move';

/**
 * Reoder an array of items.
 *
 * @param {Array} profiles Array of profiles.
 * @param {Number} oldIndex Old index.
 * @param {Number} newIndex New index.
 * @returns {Array} Array of profiles reordered.
 */
const reorderIndex = (profiles, oldIndex, newIndex) => arrayMove(
  profiles,
  oldIndex,
  newIndex,
);

export default reorderIndex;
