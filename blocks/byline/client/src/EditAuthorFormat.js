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
    removeFormat,
    getTextContent,
    slice,
  },
} = wp;

const formatName = 'byline-manager/author';

/**
 * Generates the format object that will be applied to the author text.
 *
 * @param {object}  authorName  The Author's name.
 * @param {string}  profileId  Profile Id for the Author (null if not linked)
 * @param {string}  termId     Term Id for the Author (null if not linked)
 *
 * @return {Object} The final format object.
 */
function createAuthorFormat({ authorName, profileId, termId }) {
  const format = {
    type: 'byline-manager/author',
    attributes: {
      profileId,
      termId,
    },
    text: authorName,
  };

  return format;
}

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
    addingAuthor: false,
  };

  onRemoveFormat() {
    const { value, onChange } = this.props;
    onChange(removeFormat(value, formatName));
  }

  stopAddingAuthor() {
    this.setState({ addingAuthor: false });
  }

  addAuthor() {
    const { value, onChange } = this.props;
    const selectedText = getTextContent(slice(value));

    if (selectedText) {
      onChange(
        applyFormat(
          value,
          createAuthorFormat({
            authorName: selectedText,
            profileId: '456',
            termId: '123',
          }),
        )
      );
    } else {
      this.setState({ addingAuthor: true });
    }
  }

  render() {
    const {
      isActive,
      value,
      onChange,
      activeAttributes,
    } = this.props;

    const {
      addingAuthor,
    } = this.state;

    return (
      <Fragment>
        { isActive &&
          <RichTextToolbarButton
            icon="admin-users"
            title={__('Remove Byline Author', 'byline-manager')}
            onClick={this.onRemoveFormat}
            isActive={isActive}
          />
        }
        { ! isActive &&
          <RichTextToolbarButton
            icon="admin-users"
            title={__('Add Byline Author', 'byline-manager')}
            onClick={this.addAuthor}
            isActive={isActive}
          />
        }
        <InlineAuthorUI
          addingAuthor={addingAuthor}
          stopAddingAuthor={this.stopAddingAuthor}
          isActive={isActive}
          activeAttributes={activeAttributes}
          value={value}
          onChange={onChange}
        />
      </Fragment>
    );
  }
}

export default AuthorFormatEdit;
