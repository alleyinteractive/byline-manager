/* global bylineData */

import React from 'react';
import ReactDOM from 'react-dom';
import BylineMetaBox from 'components/byline-metabox';

const initBylineMetaBox = () => {
  const { bylineMetaBox } = bylineData;

  ReactDOM.render(
    <BylineMetaBox bylineMetaBox={bylineMetaBox} />,
    document.getElementById('byline-manager-metabox-root')
  );
};

export default initBylineMetaBox;
