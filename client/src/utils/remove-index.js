/**
 * Remove profile ID from array of profiles.
 *
 * @param {Array} profiles Array of profiles.
 * @param {Number} id Profile ID to remove.
 * @returns {Array} Updated array of profiles.
 */
const removeIndex = (profiles, id) => {
  const index = profiles.findIndex((item) => item.id === id);

  if (index >= 0) {
    return profiles.filter((item) => item.id !== id);
  }

  return profiles;
};

export default removeIndex;
