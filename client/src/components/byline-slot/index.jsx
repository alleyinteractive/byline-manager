/* global bylineData */

import React, { useEffect } from 'react';
import { dispatch, useDispatch, useSelect } from '@wordpress/data';

// Components.
import createSaveByline from './createSaveByline';
import BylineAutocomplete from './bylineAutocomplete';
import BylineFreeform from './bylineFreeform';
import BylineList from './bylineList';

const BylineSlot = () => {
//   const {
//     addAuthorLabel,
//     addAuthorPlaceholder,
//     removeAuthorLabel,
//     addFreeformlabel,
//     addFreeformPlaceholder,
//     addFreeformButtonLabel,
//     linkUserPlaceholder,
//     userAlreadyLinked,
//     linkedToLabel,
//     unlinkLabel,
//     profilesApiUrl,
//     usersApiUrl,
//     postId,
//     bylineMetaBox,
//   } = bylineData;

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
      />
      <BylineFreeform
        onUpdate={addProfile}
      />
      <BylineList
        profiles={profiles}
        onSortEnd={reorderProfile}
        lockAxis="y"
        helperClass="byline-list-item"
        removeItem={removeProfile}
      />
    </div>
  );
};

export default BylineSlot;
