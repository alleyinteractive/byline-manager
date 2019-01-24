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
    Popover,
    IconButton,
  },
  element: {
    Component,
  },
  i18n: {
    __,
  },
} = wp;

// @memberof InlineAuthorUI
const AuthorEditor = ({ inputValue, onChangeInputValue }) => (
  /* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
  <form
    className="editor-format-toolbar__author-container-content"
  >
    <input
      type="text"
      aria-label={__('Author Name', 'byline-manager')}
      required
      value={inputValue}
      onChange={onChangeInputValue}
      placeholder={__('Type an author name', 'byline-manager')}
    />
    <IconButton
      icon="editor-break"
      label={__('Search', 'byline-manager')}
      disabled
      type="submit"
    />
  </form>
);

AuthorEditor.propTypes = {
  onChangeInputValue: PropTypes.func.isRequired,
  inputValue: PropTypes.object.isRequired,
};

class InlineAuthorUI extends Component {
  static propTypes = {
    addingAuthor: PropTypes.func.isRequired,
    stopAddingAuthor: PropTypes.func.isRequired,
    isActive: PropTypes.bool.isRequired,
    activeAttributes: PropTypes.object.isRequired,
    value: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired,
  };

  constructor(...args) {
    super(args);

    this.onClickOutside = this.onClickOutside.bind(this);
    this.onChangeInputValue = this.onChangeInputValue.bind(this);
    this.resetState = this.resetState.bind(this);
  }

  state = {
    inputValue: '',
  };

  onClickOutside() {
    // Save changes
    this.props.onChange(this.state.inputValue);
    this.resetState();
  }

  onChangeInputValue(inputValue) {
    this.setState({ inputValue });
  }

  resetState() {
    this.props.stopAddingAuthor();
  }

  render() {
    const {
      isActive,
      activeAttributes: {
        profileId,
        termId,
      },
      addingAuthor,
      value,
    } = this.props;

    if (! isActive && ! addingAuthor) {
      return null;
    }

    const {
      inputValue,
    } = this.state;

    return (
      <PositionedAtSelection
        key={
          `${value.start || 0}${value.end || 0}`
          /* Used to force rerender on selection change */
        }
      >
        <Popover
          className="editor-author-popover"
        >
          <AuthorEditor
            value={inputValue}
            onChangeInputValue={this.onChangeInputValue}
            profileId={profileId}
            termId={termId}
          />
        </Popover>
      </PositionedAtSelection>
    );
  }
}

export default InlineAuthorUI;
