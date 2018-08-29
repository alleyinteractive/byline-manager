/*
 * Byline Meta Box.
 */

import React, { Component } from 'react';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';
import Autocomplete from 'react-autocomplete';

const SortableItem = SortableElement(({
  bylineId,
  name,
  image,
  removeItem,
}) => (
  <li className="byline-list-item">
    <input type="hidden" name="byline_profiles[]" value={bylineId} />
    <img src={image} alt={name} />
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
        bylineId={profile.byline_id}
        name={profile.name}
        image={profile.image}
        removeItem={() => removeItem(profile.id)}
      />
    ))}
  </ol>
));

class BylineMetaBox extends Component {
  state = {
    profiles: window.bylineData.profiles || [],
    search: '',
    searchResults: [],
  };

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

  render() {
    const inputProps = {
      placeholder: window.bylineData.addAuthorPlaceholder,
      onKeyDown: (e) => {
        if (13 === e.keyCode) {
          e.preventDefault();
        }
      },
    };

    return (
      <div className="byline-list byline-manager-meta-box">
        <Autocomplete
          inputProps={inputProps}
          items={this.state.searchResults}
          value={this.state.search}
          getItemValue={(item) => item.name}
          onSelect={(value, item) => {
            this.setState({
              search: '',
              searchResults: [],
              profiles: [
                ...this.state.profiles,
                item,
              ],
            });
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
              className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
              key={item.id}
            >
              {item.name}
            </div>
          )}
        />
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

export default BylineMetaBox;
