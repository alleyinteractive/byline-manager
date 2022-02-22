import React from 'react';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/edit-post';

// Components.
import BylineSlot from 'components/byline-slot';

// Data.
import './data';

// Styles.
import './styles/styles.scss';

const BylineSlotFill = () => (
  <PluginPostStatusInfo>
    <div style={{ width: '100%' }}>
      <p>
        <strong>{__('Author Bylines', 'byline-manager')}</strong>
      </p>
      <BylineSlot />
    </div>
  </PluginPostStatusInfo>
);

registerPlugin('byline-manager', { render: BylineSlotFill });
