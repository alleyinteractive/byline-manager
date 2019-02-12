/**
 * InlineAuthorUI follows the same basic structure as the core InlineLinkUI.
 * It provides the Popover container and positioning.
 *
 * It holds the Author state, and temporary input state.
 */

/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

import PositionedAtSelection from './PositionedAtSelection';
import AuthorSelector from './AuthorSelector';

/**
 * WordPress dependencies
 */
const {
  components: {
    Popover,
  },
  element: {
    Component,
    createRef,
  },
  richText: {
    applyFormat,
    create,
    insert,
  },
} = wp;

const formatName = 'byline-manager/author';

/**
 * Helper function to generate the format object with populated attributes,
 * which will be applied to the author text.
 *
 * @param {string}  profileId  Profile Id for the Author (null if not linked)
 *
 * @return {Object} The final format object.
 */

function createAuthorFormat({ profileId }) {
  const format = {
    type: formatName,
    attributes: {
      url: `#${profileId}`,
      profileId,
    },
  };

  return format;
}

class InlineAuthorUI extends Component {
  static propTypes = {
    isActive: PropTypes.bool.isRequired,
    isAddingAuthor: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired,
    stopAddingAuthor: PropTypes.func.isRequired,
    value: PropTypes.object.isRequired,
    start: PropTypes.number.isRequired,
    end: PropTypes.number.isRequired,
  };

  // Populate defaults if we have values from the parent.
  static getDerivedStateFromProps(props, state) {
    const {
      activeAttributes: { profileId },
      authorName,
    } = props;

    const {
      authorNameInput,
      profileIdSelected,
    } = state;

    // Populate a default if we have a value from the parent.
    if (! authorNameInput && authorName) {
      return { authorNameInput: authorName };
    }

    if (! profileIdSelected && profileId) {
      return { profileIdSelected: profileId };
    }

    return null;
  }

  constructor(props, ...args) {
    super(props, args);

    this.autocompleteRef = createRef();

    this.onSubmitAuthor = this.onSubmitAuthor.bind(this);
    this.onClickOutside = this.onClickOutside.bind(this);
    this.onChangeAuthor = this.onChangeAuthor.bind(this);
    this.resetState = this.resetState.bind(this);
  }

  state = {
    authorNameInput: '',
    profileIdSelected: '',
  };

  onClickOutside(event) {
    // The autocomplete suggestions list renders in a separate popover (in a portal),
    // so onClickOutside fails to detect that a click on a suggestion occurred in the
    // AuthorContainer. Detect clicks on autocomplete suggestions using a ref here, and
    // return to avoid the popover being closed.
    const autocompleteElement = this.autocompleteRef.current;
    if (autocompleteElement && autocompleteElement.contains(event.target)) {
      return;
    }

    this.resetState();
  }

  onChangeAuthor({ authorName, profileId }) {
    this.setState({
      authorNameInput: authorName,
      profileIdSelected: profileId,
    });
  }

  onSubmitAuthor(event) {
    const {
      value,
      onChange,
      start,
      end,
    } = this.props;

    const {
      authorNameInput,
      profileIdSelected,
    } = this.state;

    const format = createAuthorFormat({
      profileId: profileIdSelected || '',
    });

    event.preventDefault();

    const toInsert = applyFormat(
      create({ text: authorNameInput }),
      format,
      0,
      authorNameInput.length
    );

    onChange(
      insert(value, toInsert, start, end)
    );

    this.resetState();
  }

  resetState() {
    this.setState({
      authorNameInput: '',
      profileIdSelected: '',
    });
    this.props.stopAddingAuthor();
  }

  render() {
    const {
      isActive,
      isAddingAuthor,
      start,
      end,
    } = this.props;

    if (! isActive && ! isAddingAuthor) {
      return null;
    }

    const {
      authorNameInput,
      profileIdSelected,
    } = this.state;

    const key = `${start || 0}${end || 0}`;

    return (
      <PositionedAtSelection
        key={key /* Used to force rerender on selection change */}
      >
        <Popover
          className="editor-byline-manager-popover"
          focusOnMount="firstElement"
          position="bottom center"
          onClose={this.resetState}
          onClickOutside={this.onClickOutside}
        >
          <AuthorSelector
            authorNameInput={authorNameInput}
            autocompleteRef={this.autocompleteRef}
            profileIdSelected={profileIdSelected}
            onChangeAuthor={this.onChangeAuthor}
            onSubmit={this.onSubmitAuthor}
          />
        </Popover>
      </PositionedAtSelection>
    );
  }
}

export default InlineAuthorUI;