/* global bylineData */

import React from 'react';
import PropTypes from 'prop-types';

// Components.
import BylineProfiles from '../byline-profiles.js';

// Proptypes.
import BYLINE_PROFILE_SHAPE from '../../../config/prop-types';

const BylineMetaBox = ({
  bylineMetaBox: {
    profiles = [],
  },
}) => {
  const {
    addAuthorLabel,
    addAuthorPlaceholder,
    addFreeformButtonLabel,
    addFreeformLabel,
    addFreeformPlaceholder,
    profilesApiUrl,
    removeAuthorLabel,
  } = bylineData;

  if (! profiles) {
    return null;
  }

  return (
    <div className="byline-list byline-manager-meta-box">
      <input type="hidden" name="byline_source" value="profiles" />
      <BylineProfiles
        addAuthorLabel={addAuthorLabel}
        addAuthorPlaceholder={addAuthorPlaceholder}
        addFreeformButtonLabel={addFreeformButtonLabel}
        addFreeformLabel={addFreeformLabel}
        addFreeformPlaceholder={addFreeformPlaceholder}
        profilesApiUrl={profilesApiUrl}
        removeAuthorLabel={removeAuthorLabel}
        profiles={profiles}

      />
    </div>
  );
};

BylineMetaBox.propTypes = {
  bylineMetaBox: PropTypes.shape({
    profiles: PropTypes.arrayOf(PropTypes.shape(BYLINE_PROFILE_SHAPE)),
  }).isRequired,
};

export default BylineMetaBox;
