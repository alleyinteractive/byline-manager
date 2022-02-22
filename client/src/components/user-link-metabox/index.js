import React, { Component } from 'react';
import Autocomplete from 'react-autocomplete';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';

class UserLinkMetaBox extends Component {
  static propTypes = {
    user: PropTypes.shape({
      id: PropTypes.number,
      name: PropTypes.string,
      linked: PropTypes.bool,
    }),
  };

  static defaultProps = {
    user: {},
  };

  static delay = null;

  constructor(props) {
    super(props);

    this.state = {
      user: props.user,
      search: '',
      searchResults: [],
    };
  }

  doUserSearch(fragment) {
    const { usersApiUrl, postId } = window.bylineData;
    fetch(
      `${usersApiUrl}?s=${fragment}&post=${postId}`,
    )
      .then((res) => res.json())
      .then((searchResults) => {
        this.setState({ searchResults });
      });
  }

  render() {
    const inputProps = {
      placeholder: window.bylineData.linkUserPlaceholder,
      onKeyDown: (e) => {
        // If the user hits 'enter', stop the parent form from submitting.
        if (13 === e.keyCode) {
          e.preventDefault();
        }
      },
    };

    return (
      <div className="profile-user-link byline-manager-meta-box">
        <input
          type="hidden"
          name="profile_user_link"
          value={this.state.user.id || ''}
        />
        {this.state.user.id && (
          <p className="current-user-link">
            {`${window.bylineData.linkedToLabel} `}
            <a href={`/wp-admin/user-edit.php?user_id=${this.state.user.id}`}>
              {this.state.user.name}
            </a>
            {' '}
            <Button
              className="button button-link-delete button-small"
              isSecondary
              variant="secondary"
              isDestructive
              isSmall
              onClick={(e) => {
                e.preventDefault();
                this.setState({ user: {} });
              }}
            >
              {window.bylineData.unlinkLabel}
            </Button>
          </p>
        )}
        <Autocomplete
          inputProps={inputProps}
          items={this.state.searchResults}
          value={this.state.search}
          getItemValue={(item) => item.name}
          onSelect={(value, user) => {
            this.setState({
              user,
              search: '',
              searchResults: [],
            });
          }}
          onChange={(event, value) => {
            clearTimeout(this.delay);
            this.setState({
              search: value,
            });

            this.delay = setTimeout(() => {
              this.doUserSearch(value);
            }, 500);
          }}
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
                <em>{window.bylineData.userAlreadyLinked}</em>}
            </div>
          )}
        />
      </div>
    );
  }
}

export default UserLinkMetaBox;
