import React from 'react';
import BylineSlot from 'components/BylineSlot';
import './data';

import './styles/styles.scss';

const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const { __ } = wp.i18n;

const BylineSlotFill = () => (
  <div>
    <PluginPostStatusInfo>
      <h3 style={{ marginBottom: 0 }}>{__('Bylines')}</h3>
    </PluginPostStatusInfo>
    <PluginPostStatusInfo>
      <BylineSlot />
    </PluginPostStatusInfo>
  </div>
);

registerPlugin('byline-manager', { render: BylineSlotFill });
