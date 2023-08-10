// External dependencies.
import React from 'react';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

// Internal dependencies.
import BylineSlot from 'components/byline-slot';
import {
  BylineManagerPanelInfoProvider,
  BylineManagerPanelInfo,
} from 'containers/byline-panel-info-container';

// Register our store.
import './store';

// Styles.
import './styles/styles.scss';

const BylineManagerSlotFill = () => (
  <BylineManagerPanelInfo>
    <p>
      <strong>{__('Byline', 'byline-manager')}</strong>
    </p>
    <BylineSlot />
  </BylineManagerPanelInfo>
);

// Register our SlotFill provider.
registerPlugin('byline-manager-panel-info-provider', { render: BylineManagerPanelInfoProvider });

// Register core slot fill.
registerPlugin('byline-manager', { render: BylineManagerSlotFill });
