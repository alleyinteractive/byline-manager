import React from 'react';
import BylineSlot from 'components/BylineSlot';

import './styles/styles.scss';

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;

const BylineSetting = () => (
  <PluginDocumentSettingPanel
    name="byline"
    title="Byline"
    className="byline"
  >
    <BylineSlot />
  </PluginDocumentSettingPanel>

);

registerPlugin('byline-setting', { render: BylineSetting });
