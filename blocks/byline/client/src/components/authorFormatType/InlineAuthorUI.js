/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

import PositionedAtSelection from './PositionedAtSelection';

/**
 * WordPress dependencies
 */
const {
  components: {
    IconButton,
    Popover,
  },
  compose: {
    withState,
  },
  element: {
    Component,
  },
  i18n: {
    __,
  },
  richText: {
    applyFormat,
    create,
    getSelectionEnd,
    getSelectionStart,
    insert,
  },
} = wp;

const formatName = 'byline-manager/author';

// @memberof InlineAuthorUI
const AuthorEditor = withState({ profileIdSelected: '' })(({
  authorNameInput,
  onSubmit,
  profileIdSelected,
  onChangeAuthorName,
}) => (
  <form
    className="editor-author-popover__author-container-content"
    onSubmit={onSubmit}
  >
    <input
      type="text"
      aria-label={__('Author Name', 'byline-manager')}
      required
      value={authorNameInput}
      onChange={onChangeAuthorName}
      placeholder={__('Type an author name', 'byline-manager')}
    />
    <input
      type="hidden"
      value={profileIdSelected}
    />
    <IconButton
      icon="editor-break"
      label={__('Enter', 'byline-manager')}
      type="submit"
    />
  </form>
));

AuthorEditor.propTypes = {
  authorNameInput: PropTypes.object.isRequired,
  submitAuthor: PropTypes.func.isRequired,
  onChangeAuthorName: PropTypes.func.isRequired,
  profileIdSelected: PropTypes.string.isRequired,
};

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
      profileId,
    },
  };

  return format;
}

class InlineAuthorUI extends Component {
  static propTypes = {
    authorName: PropTypes.string.isRequired,
    activeAttributes: PropTypes.object.isRequired,
    isActive: PropTypes.bool.isRequired,
    isAddingAuthor: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired,
    stopAddingAuthor: PropTypes.func.isRequired,
    value: PropTypes.object.isRequired,
  };

  // Populate defaults if we have values from the parent.
  static getDerivedStateFromProps(props, state) {
    const {
      activeAttributes: { profileId },
      authorName,
    } = props;

    // Populate a default if we have a value from the parent.
    if (! state.authorNameInput && authorName) {
      return { authorNameInput: authorName };
    }

    if (! state.profileIdSelected && profileId) {
      return { profileIdSelected: profileId };
    }

    return null;
  }

  constructor(props, ...args) {
    super(props, args);

    this.onSubmitAuthor = this.onSubmitAuthor.bind(this);
    this.onClickOutside = this.onClickOutside.bind(this);
    this.onChangeAuthorName = this.onChangeAuthorName.bind(this);
    this.resetState = this.resetState.bind(this);
  }

  state = {
    authorNameInput: '',
    profileIdSelected: '',
  };

  onClickOutside() {
    this.resetState();
  }

  onChangeAuthorName(event) {
    this.setState({ authorNameInput: event.target.value });
  }

  onSubmitAuthor(event) {
    const {
      value,
      onChange,
    } = this.props;
    const { authorNameInput } = this.state;
    // @todo Determine the profile values.
    const format = createAuthorFormat({
      profileId: '',
    });

    event.preventDefault();

    const toInsert = applyFormat(
      create({ text: authorNameInput }),
      format,
      0,
      authorNameInput.length
    );

    onChange(
      insert(
        value, toInsert, getSelectionStart(value), getSelectionEnd(value)
      )
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
      activeAttributes: {
        profileId,
      },
      authorName,
      isActive,
      isAddingAuthor,
      value,
    } = this.props;

    if (! isActive && ! isAddingAuthor) {
      return null;
    }

    const {
      authorNameInput,
      profileIdSelected,
    } = this.state;

    return (
      <PositionedAtSelection
        key={
          `${value.start || 0}${value.end || 0}`
          /* Used to force rerender on selection change */
        }
      >
        <Popover
          key={
            `${value.start || 0}${value.end || 0}`
            /* Used to force rerender on selection change */
          }
          className="editor-author-popover"
          focusOnMount="firstElement"
          position="bottom center"
          onClose={this.resetState}
          onClickOutside={this.onClickOutside}
        >
          <AuthorEditor
            authorName={authorName}
            authorNameInput={authorNameInput}
            profileId={profileId}
            profileIdSelected={profileIdSelected}
            onChangeAuthorName={this.onChangeAuthorName}
            onSubmit={this.onSubmitAuthor}
          />
        </Popover>
      </PositionedAtSelection>
    );
  }
}

export default InlineAuthorUI;
