/* global bylineData */

import ReactDOM from 'react-dom';
import UserLinkMetaBox from 'components/user-link-metabox';

const initUserLinkMetaBox = () => {
  const userLinkEl = document.getElementById('byline-manager-user-link-root');
  const user = userLinkEl.dataset.user
    ? JSON.parse(userLinkEl.dataset.user)
    : {};
  const {
    linkUserPlaceholder,
    linkedToLabel,
    postId,
    unlinkLabel,
    userAlreadyLinked,
    usersApiUrl,
  } = bylineData;

  ReactDOM.createRoot(userLinkEl).render(
    (
      <UserLinkMetaBox
        linkUserPlaceholder={linkUserPlaceholder}
        linkedToLabel={linkedToLabel}
        postId={+postId}
        unlinkLabel={unlinkLabel}
        user={user}
        userAlreadyLinked={userAlreadyLinked}
        usersApiUrl={usersApiUrl}
      />
    ),
  );
};

export default initUserLinkMetaBox;
