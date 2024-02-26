/* global bylineData */

import ReactDOM from 'react-dom';
import BylineMetaBox from 'components/byline-metabox';

const initBylineMetaBox = () => {
  const { bylineMetaBox } = bylineData;

  ReactDOM.createRoot(document.getElementById('byline-manager-metabox-root'))
    .render(<BylineMetaBox bylineMetaBox={bylineMetaBox} />);
};

export default initBylineMetaBox;
