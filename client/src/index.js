import React from 'react';
import BylineProfiles from 'components/BylineMetaBox/BylineProfiles';
// Entry point for all shared JS.
// import initBylineMetaBox from './initBylineMetaBox';
// import initUserLinkMetaBox from './initUserLinkMetaBox';

import './styles/styles.scss';

// if (document.getElementById('byline-manager-metabox-root')) {
//   initBylineMetaBox();
// } else if (document.getElementById('byline-manager-user-link-root')) {
//   initUserLinkMetaBox();
// }

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;

const BylineSetting = () => (
  <PluginDocumentSettingPanel
    name="byline"
    title="Byline"
    className="byline"
  >
    <BylineProfiles />
  </PluginDocumentSettingPanel>

);

registerPlugin('byline-setting', { render: BylineSetting });
