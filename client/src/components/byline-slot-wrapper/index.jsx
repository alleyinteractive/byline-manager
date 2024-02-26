/* global bylineData */

// External dependencies.
import PropTypes from 'prop-types';
import { Spinner } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

// Internal dependencies.
import BylineAutocomplete from '../byline-autocomplete';
import BylineFreeform from '../byline-freeform';
import BylineList from '../byline-list';

function BylineSlotWrapper({
  addAuthorLabel,
  addAuthorPlaceholder,
  addFreeformButtonLabel,
  addFreeformLabel,
  addFreeformPlaceholder,
  addProfile,
  autocompleteInputId,
  freeformInputId,
  profiles,
  profilesApiUrl,
  removeAuthorLabel,
  removeProfile,
  reorderProfile,
}) {
  return (
    <div className="components-base-control">
      {profiles === null ? (
        <div style={{ textAlign: 'center' }}>
          <Spinner />
        </div>
      ) : (
        <Fragment>
          <BylineAutocomplete
            id={autocompleteInputId}
            profiles={profiles}
            onUpdate={addProfile}
            profilesApiUrl={profilesApiUrl || bylineData.profilesApiUrl}
            addAuthorPlaceholder={addAuthorPlaceholder || bylineData.addAuthorPlaceholder}
            addAuthorLabel={addAuthorLabel || bylineData.addAuthorLabel}
          />
          <BylineFreeform
            id={freeformInputId}
            onUpdate={addProfile}
            addFreeformLabel={addFreeformLabel || bylineData.addFreeformLabel}
            addFreeformPlaceholder={addFreeformPlaceholder || bylineData.addFreeformPlaceholder}
            addFreeformButtonLabel={addFreeformButtonLabel || bylineData.addFreeformButtonLabel}
          />
          {profiles.length !== 0 ? (
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
}

BylineSlotWrapper.defaultProps = {
  addAuthorLabel: null,
  addAuthorPlaceholder: null,
  addFreeformButtonLabel: null,
  addFreeformLabel: null,
  addFreeformPlaceholder: null,
  autocompleteInputId: 'profiles_autocomplete',
  freeformInputId: 'byline_freeform',
  profiles: [],
  profilesApiUrl: null,
  removeAuthorLabel: null,
};

BylineSlotWrapper.propTypes = {
  addAuthorLabel: PropTypes.string,
  addAuthorPlaceholder: PropTypes.string,
  addFreeformButtonLabel: PropTypes.string,
  addFreeformLabel: PropTypes.string,
  addFreeformPlaceholder: PropTypes.string,
  addProfile: PropTypes.func.isRequired,
  autocompleteInputId: PropTypes.string,
  freeformInputId: PropTypes.string,
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
  profilesApiUrl: PropTypes.string,
  removeAuthorLabel: PropTypes.string,
  removeProfile: PropTypes.func.isRequired,
  reorderProfile: PropTypes.func.isRequired,
};

export default BylineSlotWrapper;
