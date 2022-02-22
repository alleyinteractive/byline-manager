import React from 'react';
import ReactDOM from 'react-dom';
import BylineMetaBox from 'components/byline-metabox';

const initBylineMetaBox = () => {
  ReactDOM.render(
    <BylineMetaBox />,
    document.getElementById('byline-manager-metabox-root')
  );
};

export default initBylineMetaBox;
