import transformHydratedProfiles from './transformHydratedProfiles';

/**
 * Create a function to save bylines to meta.
 */
const createSaveByline = (dispatch) => (profiles) => {
  if (! profiles.length) {
    return profiles;
  }

  const preparedProfiles = transformHydratedProfiles(profiles);

  // Save byline tax term relationships.
  const termBylines = preparedProfiles.filter(
    (value) => (value.type && 'byline_id' === value.type)
  );

  if (termBylines.length) {
    dispatch('core/editor').editPost({
      byline: [...termBylines.map((item) => item.atts.term_id)],
    });
  } else {
    dispatch('core/editor').editPost({
      byline: [],
    });
  }

  // Save profile metadata.
  return dispatch('core/editor').editPost({
    meta: {
      byline: {
        profiles: preparedProfiles,
      },
    },
  });
};

export default createSaveByline;
