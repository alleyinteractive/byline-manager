import React from 'react';
import BylineProfiles from 'components/BylineMetaBox/BylineProfiles';

import './styles/styles.scss';

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;

const BylineSetting = () => (
  <PluginDocumentSettingPanel
    name="byline"
    title="Byline"
    className="byline"
  >
    <BylineProfiles
      metaKey="byline"
      profiles={window.bylineData.bylineMetaBox.profiles || {}}
    />
  </PluginDocumentSettingPanel>

);

registerPlugin('byline-setting', { render: BylineSetting });
