/* global bylineData */

// External dependencies.
import React from 'react';
import PropTypes from 'prop-types';

// Internal dependencies.
import BylineProfiles from '../byline-profiles';

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
    profiles: PropTypes.arrayOf(PropTypes.shape({
      id: PropTypes.oneOfType([
        PropTypes.number,
        PropTypes.string,
      ]),
      byline_id: PropTypes.number,
      name: PropTypes.string,
      image: PropTypes.oneOfType([
        PropTypes.bool,
        PropTypes.string,
      ]),
    })),
  }).isRequired,
};

export default BylineMetaBox;
