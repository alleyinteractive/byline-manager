import React from 'react';
import PropTypes from 'prop-types';
import BylineProfiles from '../byline-profiles.js';

const BylineMetaBox = ({
  bylineMetaBox = {},
}) => {
  if (! bylineMetaBox) {
    return null;
  }

  return (
    <div className="byline-list byline-manager-meta-box">
      <input type="hidden" name="byline_source" value="profiles" />
      <BylineProfiles profiles={this.metaBoxData.profiles} />
    </div>
  );
};

BylineMetaBox.propTypes = {
  bylineMetaBox: PropTypes.shape({
    profiles: PropTypes.arrayOf(PropTypes.shape({
      id: PropTypes.number,
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
