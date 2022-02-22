/*
 * Byline Profiles UI.
 */

import React, { Component } from 'react';
import classNames from 'classnames';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';
import Autocomplete from 'react-autocomplete';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';

// import useDebounce from '../../services/use-debounce';

const SortableItem = SortableElement(({
  count,
  bylineId,
  name,
  image,
  removeItem,
}) => (
  <li className="byline-list-item">
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
    <Button
      label={window.bylineData.removeAuthorLabel}
      isSecondary
      isDestructive
      isSmall
      variant="secondary"
      onClick={(e) => {
        e.preventDefault();
        removeItem();
      }}
    >
      &times;
    </Button>
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
      `${window.bylineData.profilesApiUrl}?s=${fragment}`,
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = this.state.profiles.map((profile) => profile.id);
        const searchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id),
        );
        this.setState({ searchResults });
      });
  };

  generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  render() {
    const inputProps = {
      className: 'components-text-control__input',
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
        <div className="byline-list-controls components-base-control">
          <div className="profile-controls components-base-control__field">
            {/* eslint-disable jsx-a11y/label-has-for */}
            <label
              className="components-base-control__label"
              htmlFor="profiles_autocomplete"
            >
              {window.bylineData.addAuthorLabel}
            </label>
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
                  className={
                    classNames(
                      'item',
                      {
                        'item-highlighted': isHighlighted,
                      }
                    )
                  }
                  key={item.id}
                >
                  {item.name}
                </div>
              )}
            />
          </div>
          <div className="freeform-controls components-base-control__field">
            <label
              className="components-base-control__label"
              htmlFor="byline_freeform"
            >
              {window.bylineData.addFreeformlabel}
            </label>
            <div className="freeformInputGrp">
              <input
                className="components-text-control__input"
                id="byline_freeform"
                name="byline_freeform"
                onChange={(e) => {
                  this.setState({ value: e.target.value });
                }}
                placeholder={window.bylineData.addFreeformPlaceholder}
                type="text"
                value={this.state.value}
              />
              <Button
                label={window.bylineData.addFreeformButtonLabel}
                className="button"
                disabled={! this.state.value}
                isSecondary
                variant="secondary"
                isSmall
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
                style={{ marginTop: 10 }}
              >
                {window.bylineData.addFreeformButtonLabel}
              </Button>
            </div>
          </div>
        </div>
        <BylineList
          profiles={this.state.profiles}
          onSortEnd={this.onSortEnd}
          lockAxis="y"
          helperClass="byline-list-item"
          removeItem={this.removeItem}
        />
        {/* eslint-enable jsx-a11y/label-has-for */}
      </div>
    );
  }
}

export default BylineProfiles;
