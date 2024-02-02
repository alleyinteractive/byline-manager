/* global bylineData */

import ReactDOM from 'react-dom';
import BylineMetaBox from 'components/byline-metabox';

const initBylineMetaBox = () => {
  const { bylineMetaBox } = bylineData;

  ReactDOM.createRoot(
    <BylineMetaBox bylineMetaBox={bylineMetaBox} />,
    document.getElementById('byline-manager-metabox-root'),
  );
};

export default initBylineMetaBox;
