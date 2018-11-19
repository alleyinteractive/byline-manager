/*
 * Byline Profiles UI.
 */

import React, { Component } from 'react';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';
import Autocomplete from 'react-autocomplete';
import PropTypes from 'prop-types';

const SortableItem = SortableElement(({
  count,
  bylineId,
  name,
  image,
  removeItem,
  test,
}) => (
  <li className="byline-list-item">
    {test}
    <input
      type="hidden"
      name={`byline_entry[${count}][type]`}
      value={bylineId ? 'byline_id' : 'text'}
    />
    <input
      type="hidden"
      name={`byline_entry[${count}][value]`}
      value={bylineId || name}
    />
    { image && <img src={image} alt={name} /> }
    <span>{name}</span>
    <button
      aria-label={window.bylineData.removeAuthorLabel}
      onClick={(e) => {
        e.preventDefault();
        removeItem();
      }}
    >
      &times;
    </button>
  </li>
));

const BylineList = SortableContainer(({ profiles, removeItem }) => (
  <ol>
    {profiles.map((profile, index) => (
      <SortableItem
        key={`item-${profile.id}`}
        index={index}
        count={index}
        bylineId={profile.byline_id}
        name={profile.name}
        image={profile.image}
        removeItem={() => removeItem(profile.id)}
      />
    ))}
  </ol>
));

class BylineProfiles extends Component {
  static propTypes = {
    profiles: PropTypes.array,
  };

  static defaultProps = {
    profiles: [],
  };

  constructor(props) {
    super(props);

    this.state = {
      profiles: props.profiles,
      search: '',
      searchResults: [],
      value: '',
    };
  }

  onSortEnd = ({ oldIndex, newIndex }) => {
    this.setState({
      profiles: arrayMove(this.state.profiles, oldIndex, newIndex),
    });
  };

  delay = null;

  removeItem = (id) => {
    const { profiles } = this.state;
    const index = profiles.findIndex((item) => item.id === id);
    if (0 <= index) {
      this.setState({
        profiles: [
          ...profiles.slice(0, index),
          ...profiles.slice(index + 1),
        ],
      });
    }
  };

  doProfileSearch = (fragment) => {
    fetch(
      `${window.bylineData.profilesApiUrl}?s=${fragment}`
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = this.state.profiles.map((profile) => profile.id);
        const searchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id)
        );
        this.setState({ searchResults });
      });
  };

  generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  render() {
    const inputProps = {
      type: 'text',
      placeholder: window.bylineData.addAuthorPlaceholder,
      id: 'profiles_autocomplete',
      onKeyDown: (e) => {
        // If the user hits 'enter', stop the parent form from submitting.
        if (13 === e.keyCode) {
          e.preventDefault();
        }
      },
    };

    return (
      <div>
        <div className="byline-list-controls">
          <div className="profile-controls">
            {/* eslint-disable jsx-a11y/label-has-for */}
            <label htmlFor="profiles_autocomplete">
              {window.bylineData.addAuthorLabel}
              <Autocomplete
                inputProps={inputProps}
                items={this.state.searchResults}
                value={this.state.search}
                getItemValue={(item) => item.name}
                wrapperStyle={{ position: 'relative', display: 'block' }}
                onSelect={(value, item) => {
                  this.setState((state) => ({
                    search: '',
                    searchResults: [],
                    profiles: [
                      ...state.profiles,
                      item,
                    ],
                  }));
                }}
                onChange={(event, value) => {
                  clearTimeout(this.delay);
                  this.setState({
                    search: value,
                  });

                  this.delay = setTimeout(() => {
                    this.doProfileSearch(value);
                  }, 500);
                }}
                renderMenu={(children) => (
                  <div className="menu">
                    {children}
                  </div>
                )}
                renderItem={(item, isHighlighted) => (
                  <div
                    className={`item ${isHighlighted ?
                      'item-highlighted' : ''}`}
                    key={item.id}
                  >
                    {item.name}
                  </div>
                )}
              />
            </label>
            {/* eslint-enable jsx-a11y/label-has-for */}
          </div>
          <div className="freeform-controls">
            <label htmlFor="byline_freeform">
              {window.bylineData.addFreeformlabel}
              <div className="freeformInputGrp">
                <input
                  type="text"
                  placeholder={window.bylineData.addFreeformPlaceholder}
                  name="byline_freeform"
                  id="byline_freeform"
                  value={this.state.value}
                  onChange={(e) => {
                    this.setState({ value: e.target.value });
                  }}
                />
                <button
                  aria-label={window.bylineData.addFreeformButtonLabel}
                  className="button"
                  disabled={! this.state.value}
                  onClick={(e) => {
                    e.preventDefault();
                    const newItem = {
                      id: this.generateKey('text'),
                      name: this.state.value,
                    };
                    this.setState((state) => ({
                      profiles: [
                        ...state.profiles,
                        newItem,
                      ],
                      value: '',
                    }));
                  }}
                >
                  {window.bylineData.addFreeformButtonLabel}
                </button>
              </div>
            </label>
          </div>
        </div>
        <BylineList
          profiles={this.state.profiles}
          onSortEnd={this.onSortEnd}
          lockAxis="y"
          helperClass="byline-list-item"
          removeItem={this.removeItem}
        />
      </div>
    );
  }
}

export default BylineProfiles;
