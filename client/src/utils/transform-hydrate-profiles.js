/**
 * Transforms hydrated profiles to a format that can be saved to post meta.
 *
 * The schema of the post meta is not the same as the Redux store. This function
 * transforms the profiles so that they can be saved to post meta schema.
 *
 * @param {Array} items Hydrated profiles.
 * @return {Array} Array of profiles ready to be saved to post meta.
 */
const transformHydratedProfiles = (items) => {
  if (items.length <= 0) {
    return [];
  }

  return items.map((value) => {
    // Profile type.
    if (value.byline_id && typeof value.id === 'number') {
      return {
        type: 'byline_id',
        atts: {
          term_id: value.byline_id,
          post_id: value.id,
        },
      };
    }

    // Text profile.
    return {
      type: 'text',
      atts: {
        text: value.name,
      },
    };
  });
};

export default transformHydratedProfiles;
