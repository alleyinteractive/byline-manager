// External dependencies.
import React from 'react';
import PropTypes from 'prop-types';
import { useState, useEffect } from '@wordpress/element';
import Autocomplete from 'react-autocomplete';
import { Button } from '@wordpress/components';

// Hooks.
import { useDebounce } from '@uidotdev/usehooks';

const UserLinkMetaBox = ({
  linkUserPlaceholder,
  linkedToLabel,
  postId,
  unlinkLabel,
  user: rawUser,
  userAlreadyLinked,
  usersApiUrl,
}) => {
  const [search, setSearch] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [user, setUser] = useState({});

  // Debounce search string from input.
  const debouncedSearchString = useDebounce(search, 750);

  const doUserSearch = (fragment) => {
    fetch(
      `${usersApiUrl}?s=${fragment}&post=${postId}`,
    )
      .then((res) => res.json())
      .then((results) => {
        setSearchResults(results);
      });
  };

  const inputProps = {
    placeholder: linkUserPlaceholder,
    onKeyDown: (e) => {
      // If the user hits 'enter', stop the parent form from submitting.
      if (13 === e.keyCode) {
        e.preventDefault();
      }
    },
  };

  useEffect(() => {
    if ('' !== debouncedSearchString) {
      doUserSearch(debouncedSearchString);
    }
  }, [debouncedSearchString]);

  useEffect(() => {
    setUser(rawUser);
  }, []);

  return (
    <div className="profile-user-link byline-manager-meta-box">
      <input
        type="hidden"
        name="profile_user_link"
        value={user.id || 0}
      />
      {user && user.id && user.name ? (
        <p className="current-user-link">
          {`${linkedToLabel} `}
          <a href={`/wp-admin/user-edit.php?user_id=${user.id}`}>
            {user.name}
          </a>
          {' '}
          <Button
            className="button button-link-delete button-small"
            variant="secondary"
            isDestructive
            isSmall
            onClick={(e) => {
              e.preventDefault();
              setUser({});
            }}
          >
            {unlinkLabel}
          </Button>
        </p>
      ) : null}
      <Autocomplete
        inputProps={inputProps}
        items={searchResults}
        value={search}
        getItemValue={(item) => item.name}
        onSelect={(event, next) => {
          setUser(next);
          setSearch('');
          setSearchResults([]);
        }}
        onChange={(event, next) => setSearch(next)}
        isItemSelectable={(item) => ! item.linked}
        renderMenu={(children) => (
          <div className="menu">
            {children}
          </div>
        )}
        renderItem={(item, isHighlighted) => (
          <div
            className={[
              'item',
              isHighlighted ? 'item-highlighted' : '',
              item.linked ? 'item-disabled' : '',
            ].join(' ')}
            key={item.id}
          >
            {item.name}
            {item.linked &&
              <em>{userAlreadyLinked}</em>}
          </div>
        )}
        wrapperStyle={{
          display: 'block',
        }}
      />
    </div>
  );
};

UserLinkMetaBox.defaultProps = {
  user: {},
};

UserLinkMetaBox.propTypes = {
  linkUserPlaceholder: PropTypes.string.isRequired,
  linkedToLabel: PropTypes.string.isRequired,
  postId: PropTypes.number.isRequired,
  unlinkLabel: PropTypes.string.isRequired,
  user: PropTypes.shape({
    id: PropTypes.number,
    name: PropTypes.string,
    linked: PropTypes.bool,
  }),
  userAlreadyLinked: PropTypes.string.isRequired,
  usersApiUrl: PropTypes.string.isRequired,
};

export default UserLinkMetaBox;
