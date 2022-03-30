import React from 'react';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { __experimentalDivider as Divider } from '@wordpress/components';

// Components.
import BylineSlot from 'components/byline-slot';

// Data.
import './data';

// Styles.
import './styles/styles.scss';

const BylineSlotFill = () => (
  <PluginPostStatusInfo>
    <div style={{ width: '100%' }}>
      <Divider />
      <p>
        <strong>{__('Byline', 'byline-manager')}</strong>
      </p>
      <BylineSlot />
    </div>
  </PluginPostStatusInfo>
);

registerPlugin('byline-manager', { render: BylineSlotFill });
