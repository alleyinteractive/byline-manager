/* globals React */
import PropTypes from 'prop-types';
import Autocomplete from 'react-autocomplete';

class BylineAutocomplete extends React.Component {
  static propTypes = {
    byline: PropTypes.shape({
      profiles: PropTypes.arrayOf({
        id: PropTypes.string,
      }),
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
      `${window.bylineData.profilesApiUrl}?s=${fragment}`
    )
      .then((res) => res.json())
      .then((rawResults) => {
        const currentIds = this.props.byline.profiles.map(
          (profile) => profile.id
        );
        const searchResults = rawResults.filter(
          (result) => 0 > currentIds.indexOf(result.id)
        );
        this.setState({ searchResults });
      });
  };

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

          this.onUpdate([
            ...this.props.byline.profiles,
            item,
          ]);
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
    );
  }
}

BylineAutocomplete.propTypes = {
  byline: PropTypes.shape({
    profiles: PropTypes.array,
  }).isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default BylineAutocomplete;
