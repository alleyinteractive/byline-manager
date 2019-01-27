/**
 * AuthorSelector is the UI for user input to find and select Authors,
 * or to set arbitrary freeform authors.
 *
 * It holds state relating to the autocomplete.
 */

/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { throttle } from 'lodash';
import classnames from 'classnames';
import scrollIntoView from 'dom-scroll-into-view';

/**
 * WordPress dependencies
 */
const {
  components: {
    IconButton,
    Popover,
    Spinner,
  },
  compose: {
    withInstanceId,
    withState,
  },
  element: {
    Component,
    createRef,
  },
  htmlEntities: {
    decodeEntities,
  },
  i18n: {
    __,
  },
  url: {
    addQueryArgs,
  },
  apiFetch,
} = wp;

class AuthorSelector extends Component {
  static propTypes = {
    autocompleteRef: PropTypes.oneOfType([
      PropTypes.func,
      PropTypes.shape({ current: PropTypes.instanceOf(Element) }),
    ]).isRequired,
    autoFocus: PropTypes.bool.isRequired,
    authorNameInput: PropTypes.object.isRequired,
    instanceId: PropTypes.string.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onChangeAuthor: PropTypes.func.isRequired,
    profileIdSelected: PropTypes.string.isRequired,
  };

  constructor(props, ...args) {
    const { autocompleteRef } = props;

    super(props, args);

    this.onChangeInput = this.onChangeInput.bind(this);
    this.autocompleteRef = autocompleteRef || createRef();
    this.inputRef = createRef();
    this.updateSuggestions = throttle(this.updateSuggestions.bind(this), 200);
    this.suggestionNodes = [];

    this.state = {
      profiles: [],
      showSuggestions: false,
      selectedSuggestion: null,
    };
  }

  componentDidUpdate() {
    const { showSuggestions, selectedSuggestion } = this.state;
    // only have to worry about scrolling selected suggestion into view
    // when already expanded
    if (
      showSuggestions &&
      null !== selectedSuggestion &&
      ! this.scrollingIntoView
    ) {
      this.scrollingIntoView = true;
      scrollIntoView(
        this.suggestionNodes[selectedSuggestion],
        this.autocompleteRef.current,
        {
          onlyScrollIfNeeded: true,
        }
      );

      setTimeout(() => {
        this.scrollingIntoView = false;
      }, 100);
    }
  }

  componentWillUnmount() {
    delete this.suggestionsRequest;
  }

  onChangeInput(event) {
    const inputValue = event.target.value;

    this.props.onChangeAuthor(
      {
        authorName: inputValue,
      }
    );
    this.updateSuggestions(inputValue);
  }

  updateSuggestions(value) {
    // Show the suggestions after typing at least 2 characters
    // and also for URLs
    if (2 > value.length) {
      this.setState({
        showSuggestions: false,
        selectedSuggestion: null,
        loading: false,
      });

      return;
    }

    this.setState({
      showSuggestions: true,
      selectedSuggestion: null,
      loading: true,
    });

    const request = apiFetch({
      path: addQueryArgs('/byline-manager/v1/authors', {
        s: value,
        per_page: 20,
        type: 'profile',
      }),
    });

    request.then((profiles) => {
      // A fetch Promise doesn't have an abort option. It's mimicked by
      // comparing the request reference in on the instance, which is
      // reset or deleted on subsequent requests or unmounting.
      if (this.suggestionsRequest !== request) {
        return;
      }

      this.setState({
        profiles,
        loading: false,
      });
    }).catch(() => {
      if (this.suggestionsRequest === request) {
        this.setState({
          loading: false,
        });
      }
    });

    this.suggestionsRequest = request;
  }

  bindSuggestionNode(index) {
    return (ref) => {
      this.suggestionNodes[index] = ref;
    };
  }

  selectAuthor(profile) {
    this.props.onChangeAuthor(
      {
        authorName: profile.name,
        profileId: String(profile.id),
      }
    );
    this.setState({
      selectedSuggestion: null,
      showSuggestions: false,
    });
  }

  handleOnClick(post) {
    this.selectAuthor(post);
    // Move focus to the input field when a author suggestion is clicked.
    this.inputRef.current.focus();
  }

  render() {
    const {
      authorNameInput,
      autoFocus = true,
      instanceId,
      onSubmit,
      profileIdSelected,
    } = this.props;

    const {
      loading,
      profiles,
      selectedSuggestion,
      showSuggestions,
    } = this.state;

    /* eslint-disable jsx-a11y/no-autofocus */
    return (
      <form
        className="editor-byline-manager-popover__author-container-content"
        onSubmit={onSubmit}
      >
        <div className="editor-byline-manager-input">
          <input
            autoFocus={autoFocus}
            type="text"
            aria-label={__('Author Name', 'byline-manager')}
            required
            value={authorNameInput}
            onChange={this.onChangeInput}
            placeholder={__('Type an author name', 'byline-manager')}
            ref={this.inputRef}
          />
          <input
            type="hidden"
            value={profileIdSelected}
          />
        </div>

        {(loading) && <Spinner />}

        { showSuggestions && !! profiles.length &&
          <Popover position="bottom" noArrow focusOnMount={false}>
            <div
              className="editor-byline-manager__suggestions"
              id={`editor-byline-manager-suggestions-${instanceId}`}
              ref={this.autocompleteRef}
              role="listbox"
            >
              {profiles.map((profile, index) => (
                <button
                  key={profile.id}
                  role="option"
                  tabIndex="-1"
                  id={
                    `editor-byline-manager-suggestion-${instanceId}-${index}`
                  }
                  ref={this.bindSuggestionNode(index)}
                  className={
                    classnames(
                      'editor-byline-manager__suggestion',
                      { 'is-selected': index === selectedSuggestion }
                    )
                  }
                  onClick={() => this.handleOnClick(profile)}
                  aria-selected={index === selectedSuggestion}
                >
                  {
                    decodeEntities(profile.name) ||
                    __('(no name)', 'byline-manager')
                  }
                </button>
              ))}
            </div>
          </Popover>
        }
        <IconButton
          icon="editor-break"
          label={__('Enter', 'byline-manager')}
          type="submit"
        />
      </form>
    );
    /* eslint-enable jsx-a11y/no-autofocus */
  }
}

export default withState(
  {
    profileIdSelected: '',
  }
)(withInstanceId(AuthorSelector));
