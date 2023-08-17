// Internal dependencies.
import transformHydratedProfiles from './transform-hydrate-profiles';

/**
 * Save bylines to meta.
 *
 * @param {Function} dispatch Dispatch function.
 * @param {string} metaKey Meta key to save bylines to.
 * @return {Function} Function to save bylines to meta.
 */
const setBylineMeta = (dispatch, metaKey) => (profiles) => {
  const preparedProfiles = transformHydratedProfiles(profiles);

  // Save byline tax term relationships.
  const termBylines = preparedProfiles.filter(
    (value) => (value.type && 'byline_id' === value.type)
  );

  const bylineTerm = termBylines.length ?
    [...termBylines.map((item) => item.atts.term_id)] :
    [];

  // Save profile metadata.
  return dispatch('core/editor').editPost({
    byline: bylineTerm,
    meta: {
      [metaKey]: {
        profiles: preparedProfiles,
      },
    },
  });
};

export default setBylineMeta;
