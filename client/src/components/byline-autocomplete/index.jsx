// External dependencies.
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useState, useEffect, useCallback } from '@wordpress/element';
import classNames from 'classnames';
import PropTypes from 'prop-types';
import Autocomplete from 'react-autocomplete';

// Hooks.
import { useDebounce } from '@uidotdev/usehooks';

function BylineAutocomplete({
  id,
  profiles,
  onUpdate,
  profilesApiUrl,
  addAuthorPlaceholder,
  addAuthorLabel,
}) {
  const [search, setSearch] = useState('');
  const [searchResults, setSearchResults] = useState([]);

  // Debounce search string from input.
  const debouncedSearchString = useDebounce(search, 750);

  const doProfileSearch = useCallback((fragment) => {
    console.log('url', profilesApiUrl);
    apiFetch({ url: addQueryArgs(profilesApiUrl, { s: fragment }) })
      .then((rawResults) => {
        const currentIds = profiles.map((profile) => profile.id);
        const newSearchResults = rawResults.filter(
          (result) => currentIds.indexOf(result.id) < 0,
        );
        setSearchResults(newSearchResults);
      });
  }, [profilesApiUrl, profiles]);

  const inputProps = {
    className: 'components-text-control__input',
    type: 'text',
    placeholder: addAuthorPlaceholder,
    id,
    onKeyDown: (e) => {
      // If the user hits 'enter', stop the parent form from submitting.
      if (e.keyCode === 13) {
        e.preventDefault();
      }
    },
  };

  useEffect(() => {
    if (debouncedSearchString !== '') {
      doProfileSearch(debouncedSearchString);
    }
  }, [debouncedSearchString, doProfileSearch]);

  return (
    <div className="profile-controls components-base-control__field">
      <label
        className="components-base-control__label"
        htmlFor={id}
      >
        {addAuthorLabel}
      </label>
      <Autocomplete
        inputProps={inputProps}
        items={searchResults}
        value={search}
        getItemValue={(item) => item.name}
        wrapperStyle={{ position: 'relative', display: 'block' }}
        onSelect={(__, item) => {
          setSearch('');
          setSearchResults([]);

          onUpdate(item);
        }}
        onChange={(__, next) => setSearch(next)}
        renderMenu={(children) => (
          <div className="menu">
            {children}
          </div>
        )}
        renderItem={(item, isHighlighted) => (
          <div
            key={item.id}
            className={
              classNames(
                'item',
                {
                  'item-highlighted': isHighlighted,
                },
              )
            }
          >
            {item.name}
          </div>
        )}
        renderInput={(props) => <input {...props} style={{ width: '100%' }} />}
      />
    </div>
  );
}

BylineAutocomplete.defaultProps = {
  id: 'profiles_autocomplete',
};

BylineAutocomplete.propTypes = {
  id: PropTypes.string,
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
  onUpdate: PropTypes.func.isRequired,
  profilesApiUrl: PropTypes.string.isRequired,
  addAuthorPlaceholder: PropTypes.string.isRequired,
  addAuthorLabel: PropTypes.string.isRequired,
};

export default BylineAutocomplete;
