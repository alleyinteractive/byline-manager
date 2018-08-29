import React, { Component } from 'react';
import Autocomplete from 'react-autocomplete';
import PropTypes from 'prop-types';

class UserLinkMetaBox extends Component {
  static propTypes = {
    user: PropTypes.shape({
      id: PropTypes.number,
      name: PropTypes.string,
    }),
  };

  static defaultProps = {
    user: {},
  };

  static delay = null;

  constructor(props) {
    super(props);

    const search = props.user && props.user.name ? props.user.name : '';

    this.state = {
      user: props.user,
      search,
      searchResults: [],
    };
  }

  doUserSearch(fragment) {
    fetch(
      `${window.bylineData.usersApiUrl}?s=${fragment}`
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
        <Autocomplete
          inputProps={inputProps}
          items={this.state.searchResults}
          value={this.state.search}
          getItemValue={(item) => item.name}
          onSelect={(value, user) => {
            this.setState({
              user,
              search: user.name,
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
          renderMenu={(children) => (
            <div className="menu">
              {children}
            </div>
          )}
          renderItem={(item, isHighlighted) => (
            <div
              className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
              key={item.id}
            >
              {item.name}
            </div>
          )}
        />
      </div>
    );
  }
}

export default UserLinkMetaBox;
