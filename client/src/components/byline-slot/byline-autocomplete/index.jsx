import React from 'react';
import PropTypes from 'prop-types';
import Autocomplete from 'react-autocomplete';

class BylineAutocomplete extends React.Component {
  static propTypes = {
    profiles: PropTypes.arrayOf({
      id: PropTypes.string,
    }).isRequired,
    onUpdate: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.onUpdate = props.onUpdate;
    this.state = {
      search: '',
      searchResults: [],
    };
  }

  delay = null;

  doProfileSearch = (fragment) => {
    fetch(
      `${window.bylineData.profilesApiUrl}?s=${fragment}`,
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = this.props.profiles.map(
          (profile) => profile.id,
        );
        const searchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id),
        );
        this.setState({ searchResults });
      });
  };

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
            this.setState(() => ({
              search: '',
              searchResults: [],
            }));

            this.onUpdate(item);
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
        {/* eslint-enable jsx-a11y/label-has-for */}
      </div>
    );
  }
}

export default BylineAutocomplete;
