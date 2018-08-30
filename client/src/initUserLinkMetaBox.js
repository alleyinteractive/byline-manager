import React from 'react';
import ReactDOM from 'react-dom';
import UserLinkMetaBox from 'components/UserLinkMetaBox';

const initUserLinkMetaBox = () => {
  const userLinkEl = document.getElementById('byline-manager-user-link-root');
  const user = userLinkEl.dataset.user ?
    JSON.parse(userLinkEl.dataset.user) :
    {};

  ReactDOM.render(
    <UserLinkMetaBox user={user} />,
    userLinkEl
  );
};

export default initUserLinkMetaBox;
