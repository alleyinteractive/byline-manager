/* global bylineData */

// External dependencies.
import React from 'react';
import PropTypes from 'prop-types';
import { Spinner } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

// Internal dependencies.
import BylineAutocomplete from '../byline-autocomplete';
import BylineFreeform from '../byline-freeform';
import BylineList from '../byline-list';

const BylineSlotWrapper = ({
  profiles,
  addProfile,
  removeProfile,
  reorderProfile,
  addAuthorLabel,
  addAuthorPlaceholder,
  removeAuthorLabel,
  addFreeformLabel,
  addFreeformPlaceholder,
  addFreeformButtonLabel,
  profilesApiUrl,
}) => (
  <div className="components-base-control">
    {null === profiles ? (
      <div style={{ textAlign: 'center' }}>
        <Spinner />
      </div>
    ) : (
      <Fragment>
        <BylineAutocomplete
          profiles={profiles}
          onUpdate={addProfile}
          profilesApiUrl={profilesApiUrl || bylineData.profilesApiUrl}
          addAuthorPlaceholder={addAuthorPlaceholder || bylineData.addAuthorPlaceholder}
          addAuthorLabel={addAuthorLabel || bylineData.addAuthorLabel}
        />
        <BylineFreeform
          onUpdate={addProfile}
          addFreeformLabel={addFreeformLabel || bylineData.addFreeformLabel}
          addFreeformPlaceholder={addFreeformPlaceholder || bylineData.addFreeformPlaceholder}
          addFreeformButtonLabel={addFreeformButtonLabel || bylineData.addFreeformButtonLabel}
        />
        {0 !== profiles.length ? (
          <BylineList
            profiles={profiles}
            onSortEnd={reorderProfile}
            lockAxis="y"
            helperClass="byline-list-item"
            removeItem={removeProfile}
            removeAuthorLabel={removeAuthorLabel || bylineData.removeAuthorLabel}
          />
        ) : null}
      </Fragment>
    )}
  </div>
);

BylineSlotWrapper.defaultProps = {
  profiles: [],
  addAuthorLabel: null,
  addAuthorPlaceholder: null,
  removeAuthorLabel: null,
  addFreeformLabel: null,
  addFreeformPlaceholder: null,
  addFreeformButtonLabel: null,
  profilesApiUrl: null,
};

BylineSlotWrapper.propTypes = {
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
  addProfile: PropTypes.func.isRequired,
  removeProfile: PropTypes.func.isRequired,
  reorderProfile: PropTypes.func.isRequired,
  addAuthorLabel: PropTypes.string,
  addAuthorPlaceholder: PropTypes.string,
  removeAuthorLabel: PropTypes.string,
  addFreeformLabel: PropTypes.string,
  addFreeformPlaceholder: PropTypes.string,
  addFreeformButtonLabel: PropTypes.string,
  profilesApiUrl: PropTypes.string,
};

export default BylineSlotWrapper;
