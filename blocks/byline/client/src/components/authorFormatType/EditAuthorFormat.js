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
import { find } from 'lodash';

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
    isCollapsed,
    removeFormat,
    slice,
    getSelectionEnd,
    getSelectionStart,
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

  // Populate defaults if we have values from the parent.
  static getDerivedStateFromProps(props) {
    const {
      isActive,
      value,
    } = props;

    const { formats } = value;
    let startIndex = getSelectionStart(value);
    let endIndex = getSelectionEnd(value);
    const format = find(formats[startIndex], { type: formatName });

    // Always expand the selection to completely include the entire active Author name.
    if (isActive) {
      if (isCollapsed(value)) {
        /* eslint-disable no-plusplus*/
        while (find(formats[startIndex], format)) {
          startIndex --;
        }
        startIndex ++;

        endIndex ++;
        while (find(formats[endIndex], format)) {
          endIndex ++;
        }
        /* eslint-enable no-plusplus*/
      }

      return {
        activeText: getTextContent(slice(value, startIndex, endIndex)),
        activeFormatStart: startIndex,
        activeFormatEnd: endIndex,
      };
    }

    return null;
  }

  constructor(props, ...args) {
    super(props, args);

    this.addAuthor = this.addAuthor.bind(this);
    this.stopAddingAuthor = this.stopAddingAuthor.bind(this);
    this.onRemoveFormat = this.onRemoveFormat.bind(this);
  }

  state = {
    isAddingAuthor: false,
    activeText: '',
    activeFormatStart: null,
    activeFormatEnd: null,
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
      // Initially, format the text as a freeform author with no connected Profile.
      // User selection within the InlineAuthorUI might override this.
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
    }

    // And set state to activate the author editor.
    this.setState({ activeText: selectedText });

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
      activeFormatStart,
      activeFormatEnd,
      activeText,
      isAddingAuthor,
    } = this.state;

    const authorUIKey = `author: ${activeText}`;

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
          authorName={activeText}
          activeAttributes={activeAttributes}
          value={value}
          start={activeFormatStart}
          end={activeFormatEnd}
          onChange={onChange}
        />
      </Fragment>
    );
  }
}

export default AuthorFormatEdit;
