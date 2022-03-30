/*
 * Byline Profiles UI.
 */

import React, { useState, useEffect } from 'react';
import classNames from 'classnames';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';
import Autocomplete from 'react-autocomplete';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';

// Hooks.
import useDebounce from '../../services/use-debounce';

const SortableItem = SortableElement(({
  count,
  bylineId,
  name,
  image,
  removeItem,
  removeAuthorLabel,
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
    { image ? <img src={image} alt={name} /> : null }
    <span>{name}</span>
    <Button
      label={removeAuthorLabel}
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

const BylineList = SortableContainer(({
  profiles,
  removeItem,
  removeAuthorLabel,
}) => (
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
        removeAuthorLabel={removeAuthorLabel}
      />
    ))}
  </ol>
));

const BylineProfiles = ({
  addAuthorLabel,
  addAuthorPlaceholder,
  addFreeformButtonLabel,
  addFreeformlabel,
  addFreeformPlaceholder,
  profiles: profilesRaw,
  profilesApiUrl,
  removeAuthorLabel,
}) => {
  const [profiles, setProfiles] = useState(profilesRaw);
  const [search, setSearch] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [value, setValue] = useState('');

  // Debounce search string from input.
  const debouncedSearchString = useDebounce(search, 750);

  const onSortEnd = ({ oldIndex, newIndex }) => {
    setProfiles(arrayMove(profiles, oldIndex, newIndex));
  };

  const removeItem = (id) => {
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

  const doProfileSearch = (fragment) => {
    fetch(
      `${profilesApiUrl}?s=${fragment}`,
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = profiles.map((profile) => profile.id);
        const newSearchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id),
        );
        setSearchResults({ newSearchResults });
      });
  };

  const generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  const inputProps = {
    className: 'components-text-control__input',
    type: 'text',
    placeholder: addAuthorPlaceholder,
    id: 'profiles_autocomplete',
    onKeyDown: (e) => {
      // If the user hits 'enter', stop the parent form from submitting.
      if (13 === e.keyCode) {
        e.preventDefault();
      }
    },
  };

  useEffect(() => {
    if ('' !== debouncedSearchString) {
      doProfileSearch(debouncedSearchString);
    }
  }, [debouncedSearchString]);

  return (
    <div>
      <div className="byline-list-controls components-base-control">
        <div className="profile-controls components-base-control__field">
          {/* eslint-disable jsx-a11y/label-has-for */}
          <label
            className="components-base-control__label"
            htmlFor="profiles_autocomplete"
          >
            {addAuthorLabel}
          </label>
          <Autocomplete
            inputProps={inputProps}
            items={searchResults}
            value={search}
            getItemValue={(item) => item.name}
            wrapperStyle={{ position: 'relative', display: 'block' }}
            onSelect={(item, next) => {
              setSearch('');
              setSearchResults([]);
              setProfiles([...profiles, next]);
            }}
            onChange={(event, next) => setSearch(next)}
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
            {addFreeformlabel}
          </label>
          <div className="freeformInputGrp">
            <input
              className="components-text-control__input"
              id="byline_freeform"
              name="byline_freeform"
              onChange={(e) => {
                setValue(e.target.value);
              }}
              placeholder={addFreeformPlaceholder}
              type="text"
              value={value}
            />
            <Button
              label={addFreeformButtonLabel}
              className="button"
              disabled={! value}
              isSecondary
              variant="secondary"
              isSmall
              onClick={(e) => {
                e.preventDefault();
                const newItem = {
                  id: generateKey('text'),
                  name: value,
                };
                setProfiles([
                  ...profiles,
                  newItem,
                ]);
                setValue('');
              }}
              style={{ marginTop: 10 }}
            >
              {addFreeformButtonLabel}
            </Button>
          </div>
        </div>
      </div>
      <BylineList
        profiles={profiles}
        onSortEnd={onSortEnd}
        lockAxis="y"
        helperClass="byline-list-item"
        removeItem={removeItem}
        removeAuthorLabel={removeAuthorLabel}
      />
      {/* eslint-enable jsx-a11y/label-has-for */}
    </div>
  );
};

BylineProfiles.propTypes = {
  addAuthorLabel: PropTypes.string.isRequired,
  addAuthorPlaceholder: PropTypes.string.isRequired,
  addFreeformButtonLabel: PropTypes.string.isRequired,
  addFreeformlabel: PropTypes.string.isRequired,
  addFreeformPlaceholder: PropTypes.string.isRequired,
  profilesApiUrl: PropTypes.string.isRequired,
  removeAuthorLabel: PropTypes.string.isRequired,
  profiles: PropTypes.arrayOf(PropTypes.shape({
    id: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string,
    ]),
    byline_id: PropTypes.number,
    name: PropTypes.string,
    image: PropTypes.oneOfType([
      PropTypes.bool,
      PropTypes.string,
    ]),
  })).isRequired,
};

export default BylineProfiles;
