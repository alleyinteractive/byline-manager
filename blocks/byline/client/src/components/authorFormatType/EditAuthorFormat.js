/**
 * These are the Formatting Controls for adding and removing Authors,
 * and triggering the Author popover UI.
 *
 * It holds state relating to the selection.
 */

/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

import InlineAuthorUI from './InlineAuthorUI';

/**
 * WordPress dependencies
 */
const {
  editor: {
    RichTextToolbarButton,
  },
  element: {
    Component,
    Fragment,
  },
  i18n: {
    __,
  },
  richText: {
    applyFormat,
    getTextContent,
    removeFormat,
    slice,
  },
} = wp;

const formatName = 'byline-manager/author';

class AuthorFormatEdit extends Component {
  static propTypes = {
    activeAttributes: PropTypes.object.isRequired,
    isActive: PropTypes.bool.isRequired,
    value: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired,
  };

  constructor(...args) {
    super(args);

    this.addAuthor = this.addAuthor.bind(this);
    this.stopAddingAuthor = this.stopAddingAuthor.bind(this);
    this.onRemoveFormat = this.onRemoveFormat.bind(this);
  }

  state = {
    isAddingAuthor: false,
    selectedText: '',
  };

  onRemoveFormat() {
    const { value, onChange } = this.props;
    onChange(removeFormat(value, formatName));
  }

  stopAddingAuthor() {
    this.setState({ isAddingAuthor: false });
  }

  addAuthor() {
    const { value, onChange } = this.props;
    const selectedText = getTextContent(slice(value));

    if (selectedText) {
      // Immediately set the text as a freeform author with no connected Profile.
      onChange(
        applyFormat(
          value,
          {
            type: formatName,
            attributes: {
              profileId: '',
            },
          }
        )
      );

      this.setState({ selectedText });
    }

    // And set state to activate the author editor.
    this.setState({ isAddingAuthor: true });
  }

  render() {
    const {
      activeAttributes,
      isActive,
      onChange,
      value,
    } = this.props;

    const {
      selectedText,
      isAddingAuthor,
    } = this.state;

    const authorUIKey = `author:${selectedText}`;

    return (
      <Fragment>
        { isActive &&
          <RichTextToolbarButton
            icon="id"
            title={__('Remove Byline Author', 'byline-manager')}
            onClick={this.onRemoveFormat}
            isActive={isActive}
          />
        }
        { ! isActive &&
          <RichTextToolbarButton
            icon="id"
            title={__('Add Byline Author', 'byline-manager')}
            onClick={this.addAuthor}
            isActive={isActive}
          />
        }
        <InlineAuthorUI
          isActive={isActive}
          key={authorUIKey}
          isAddingAuthor={isAddingAuthor}
          stopAddingAuthor={this.stopAddingAuthor}
          authorName={selectedText}
          activeAttributes={activeAttributes}
          value={value}
          onChange={onChange}
        />
      </Fragment>
    );
  }
}

export default AuthorFormatEdit;
