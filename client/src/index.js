// Entry point for all shared JS.
import initBylineMetaBox from './initBylineMetaBox';
import initUserLinkMetaBox from './initUserLinkMetaBox';

import './styles/styles.scss';

if (document.getElementById('byline-manager-metabox-root')) {
  initBylineMetaBox();
} else if (document.getElementById('byline-manager-user-link-root')) {
  initUserLinkMetaBox();
}
