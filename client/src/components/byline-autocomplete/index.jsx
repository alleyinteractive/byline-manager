import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import Autocomplete from 'react-autocomplete';

// Hooks.
import useDebounce from '../../services/use-debounce';

const BylineAutocomplete = ({
  profiles,
  onUpdate,
  profilesApiUrl,
  addAuthorPlaceholder,
  addAuthorLabel,
}) => {
  const [search, setSearch] = useState('');
  const [searchResults, setSearchResults] = useState([]);

  // Debounce search string from input.
  const debouncedSearchString = useDebounce(search, 750);

  const doProfileSearch = (fragment) => {
    fetch(
      `${profilesApiUrl}?s=${fragment}`,
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = profiles.map(
          (profile) => profile.id,
        );
        const newSearchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id),
        );
        setSearchResults(newSearchResults);
      });
  };

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
    <div className="profile-controls components-base-control__field">
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
        onSelect={(value, item) => {
          setSearch('');
          setSearchResults([]);

          onUpdate(item);
        }}
        onChange={(event, next) => setSearch(next)}
        renderMenu={(children) => (
          <div
            className="menu"
            style={{
              border: '1px solid black',
              borderBottom: 0,
              borderTop: 0,
              padding: 0,
            }}
          >
            {children}
          </div>
        )}
        renderItem={(item, isHighlighted) => (
          <div
            className={`item ${isHighlighted ?
              'item-highlighted' : ''}`}
            key={item.id}
            style={{
              borderBottom: '1px solid black',
              padding: '10px',
            }}
          >
            {item.name}
          </div>
        )}
        renderInput={(props) =>
          <input {...props} style={{ width: '100%' }} />
        }
      />
    </div>
  );
};

BylineAutocomplete.propTypes = {
  profiles: PropTypes.arrayOf({
    id: PropTypes.string,
  }).isRequired,
  onUpdate: PropTypes.func.isRequired,
  profilesApiUrl: PropTypes.string.isRequired,
  addAuthorPlaceholder: PropTypes.string.isRequired,
  addAuthorLabel: PropTypes.string.isRequired,
};

export default BylineAutocomplete;
