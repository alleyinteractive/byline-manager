// External dependencies.
import PropTypes from 'prop-types';
import { useState, useEffect } from '@wordpress/element';
import classNames from 'classnames';
import {
  SortableContainer,
  SortableElement,
  arrayMove,
} from 'react-sortable-hoc';
import Autocomplete from 'react-autocomplete';
import { Button } from '@wordpress/components';

// Hooks.
import { useDebounce } from '@uidotdev/usehooks';

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
      isDestructive
      size="small"
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
  autocompleteInputId,
  freeformInputId,
  addAuthorLabel,
  addAuthorPlaceholder,
  addFreeformButtonLabel,
  addFreeformLabel,
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
      setProfiles([
        ...profiles.slice(0, index),
        ...profiles.slice(index + 1),
      ]);
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
        setSearchResults(newSearchResults);
      });
  };

  const generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  const inputProps = {
    className: 'components-text-control__input',
    type: 'text',
    placeholder: addAuthorPlaceholder,
    id: autocompleteInputId,
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
            htmlFor={autocompleteInputId}
          >
            {addAuthorLabel}
          </label>
          <Autocomplete
            inputProps={inputProps}
            items={searchResults}
            value={search}
            getItemValue={(item) => item.name}
            wrapperStyle={{ position: 'relative', display: 'block' }}
            onSelect={(__, next) => {
              setSearch('');
              setSearchResults([]);
              setProfiles([...profiles, next]);
            }}
            onChange={(__, next) => setSearch(next)}
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
            htmlFor={freeformInputId}
          >
            {addFreeformLabel}
          </label>
          <div className="freeformInputGrp">
            <input
              className="components-text-control__input"
              id={freeformInputId}
              name={freeformInputId}
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
              size="small"
              variant="secondary"
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

BylineProfiles.defaultProps = {
  autocompleteInputId: 'profiles_autocomplete',
  freeformInputId: 'byline_freeform',
};

BylineProfiles.propTypes = {
  autocompleteInputId: PropTypes.string,
  freeformInputId: PropTypes.string,
  addAuthorLabel: PropTypes.string.isRequired,
  addAuthorPlaceholder: PropTypes.string.isRequired,
  addFreeformButtonLabel: PropTypes.string.isRequired,
  addFreeformLabel: PropTypes.string.isRequired,
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
