/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const {
  editor: {
    RichTextToolbarButton,
  },
  element: {
    Component,
  },
  i18n: {
    __,
  },
  richText: {
    toggleFormat,
  },
} = wp;

class AuthorFormatEdit extends Component {
  static propTypes = {
    isActive: PropTypes.bool.isRequired,
    value: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired,
  };

  render() {
    const {
      isActive,
      value,
      onChange,
    } = this.props;

    return (
      <RichTextToolbarButton
        icon="admin-users"
        title={__('Select Byline Author', 'byline-manager')}
        onClick={() => {
          onChange(
            toggleFormat(
              value, {
                type: 'dj/byline-author',
                attributes: {
                  profileId: '456',
                  termId: '123',
                },
              }
            )
          );
        }}
        isActive={isActive}
      />
    );
  }
}

export default AuthorFormatEdit;
