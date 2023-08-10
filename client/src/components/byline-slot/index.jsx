// External dependencies.
import React from 'react';

// Internal dependencies.
import BylineSlotContainer from '../../containers/byline-slot-container';
import { store as bylineManagerStore } from '../../store';

const BylineSlot = () => (
  <BylineSlotContainer
    store={bylineManagerStore}
  />
);

export default BylineSlot;
