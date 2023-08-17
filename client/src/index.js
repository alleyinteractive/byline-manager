import initBylineMetaBox from './init-byline-metabox';
import initUserLinkMetaBox from './init-user-link-metabox';

import './styles/styles.scss';

if (document.getElementById('byline-manager-metabox-root')) {
  initBylineMetaBox();
} else if (document.getElementById('byline-manager-user-link-root')) {
  initUserLinkMetaBox();
}
