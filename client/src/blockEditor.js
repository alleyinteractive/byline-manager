import React from 'react';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import BylineSlot from 'components/BylineSlot';
import './data';

import './styles/styles.scss';

const BylineSlotFill = () => (
  <div>
    <PluginPostStatusInfo>
      <h3 style={{ marginBottom: 0 }}>{__('Bylines', 'byline-manager')}</h3>
    </PluginPostStatusInfo>
    <PluginPostStatusInfo>
      <BylineSlot />
    </PluginPostStatusInfo>
  </div>
);

registerPlugin('byline-manager', { render: BylineSlotFill });
