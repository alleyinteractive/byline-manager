/* global bylineData */

import React, { useEffect } from 'react';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';

// Components.
import createSaveByline from '../../utils/create-save-byline';
import BylineAutocomplete from '../byline-autocomplete';
import BylineFreeform from '../byline-freeform';
import BylineList from '../byline-list';

const BylineSlot = () => {
  const {
    addAuthorLabel,
    addAuthorPlaceholder,
    removeAuthorLabel,
    addFreeformLabel,
    addFreeformPlaceholder,
    addFreeformButtonLabel,
    profilesApiUrl,
  } = bylineData;

  const profiles = useSelect(
    (select) => select('byline-manager').getProfiles()
  );

  const {
    actionAddProfile: addProfile,
    actionRemoveProfile: removeProfile,
    actionReorderProfile: reorderProfile,
  } = useDispatch('byline-manager');

  const saveByline = createSaveByline(dispatch);

  useEffect(() => {
    saveByline(profiles);
  }, [profiles]);

  return (
    <div className="components-base-control">
      <BylineAutocomplete
        profiles={profiles}
        onUpdate={addProfile}
        profilesApiUrl={profilesApiUrl}
        addAuthorPlaceholder={addAuthorPlaceholder}
        addAuthorLabel={addAuthorLabel}
      />
      <BylineFreeform
        onUpdate={addProfile}
        addFreeformLabel={addFreeformLabel}
        addFreeformPlaceholder={addFreeformPlaceholder}
        addFreeformButtonLabel={addFreeformButtonLabel}
      />
      <BylineList
        profiles={profiles}
        onSortEnd={reorderProfile}
        lockAxis="y"
        helperClass="byline-list-item"
        removeItem={removeProfile}
        removeAuthorLabel={removeAuthorLabel}
      />
    </div>
  );
};

export default BylineSlot;
