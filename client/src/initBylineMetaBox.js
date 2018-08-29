import React from 'react';
import ReactDOM from 'react-dom';
import BylineMetaBox from 'components/BylineMetaBox';

const initBylineMetaBox = () => {
  ReactDOM.render(
    <BylineMetaBox />,
    document.getElementById('byline-manager-metabox-root')
  );
};

export default initBylineMetaBox;
