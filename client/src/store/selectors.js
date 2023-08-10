/**
 * Returns the list of Profiles.
 *
 * @param {Object} state The current state.
 * @returns {array|null} The list of Profiles or null if not hydrated.
 */
const getProfiles = (state) => (
  state.profilesHydrated ? state.byline.profiles || [] : null
);

export default getProfiles;
