import React from 'react';
import BylineProfiles from 'components/BylineMetaBox/BylineProfiles';

import './styles/styles.scss';

const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const { __ } = wp.i18n;

const BylineSlotFill = () => (
  <div>
    <PluginPostStatusInfo>
      <h3 style={{ 'margin-bottom': 0 }}>{__('Bylines')}</h3>
    </PluginPostStatusInfo>
    <PluginPostStatusInfo>
      <BylineProfiles profiles={window.bylineData.bylineMetaBox || {}} />
    </PluginPostStatusInfo>
  </div>
);

registerPlugin('byline-manager', { render: BylineSlotFill });
