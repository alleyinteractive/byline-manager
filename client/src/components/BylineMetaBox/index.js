/*
 * Main App container
 * Defines app views: search & detail
 */

import React, { Component } from 'react';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';

const SortableItem = SortableElement(({ name }) => (
  <li>
    <img src="//placehold.it/50x50" alt="{name}" />
    <span>{name}</span>
  </li>
));

const BylineList = SortableContainer(({ profiles }) => (
  <ol>
    {profiles.map((profile, index) => (
      <SortableItem
        key={`item-${profile.id}`}
        index={index}
        name={profile.name}
      />
    ))}
  </ol>
));

class BylineMetaBox extends Component {
  state = {
    profiles: [
      {
        id: 123,
        name: 'John Smith',
      },
      {
        id: 456,
        name: 'Jane Doe',
      },
    ],
  };

  onSortEnd = ({ oldIndex, newIndex }) => {
    this.setState({
      profiles: arrayMove(this.state.profiles, oldIndex, newIndex),
    });
  };

  render() {
    return (
      <BylineList
        profiles={this.state.profiles}
        onSortEnd={this.onSortEnd}
        lockAxis="y"
      />
    );
  }
}

export default BylineMetaBox;
