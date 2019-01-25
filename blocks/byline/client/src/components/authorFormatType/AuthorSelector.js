/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const {
  components: {
    IconButton,
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
} = wp;

class AuthorSelector extends Component {
  static propTypes = {
    authorNameInput: PropTypes.object.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onChangeAuthorName: PropTypes.func.isRequired,
    profileIdSelected: PropTypes.string.isRequired,
  };

  render() {
    const {
      // authorName,
      authorNameInput,
      onSubmit,
      // profileId,
      profileIdSelected,
      onChangeAuthorName,
    } = this.props;

    return (
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
    );
  }
}

export default withState({ profileIdSelected: '' })(AuthorSelector);
