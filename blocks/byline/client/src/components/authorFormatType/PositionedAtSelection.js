/**
 * This is a recreation of the wp.formatLibrary link popover positioning helper.
 */

/* global wp */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const {
  dom: {
    getOffsetParent,
    getRectangleFromRange,
  },
  element: {
    Component,
  },
} = wp;

/**
 * Returns a style object for applying as `position: absolute` for an element
 * relative to the bottom-center of the current selection. Includes `top` and
 * `left` style properties.
 *
 * @return {Object} Style object.
 */
function getCurrentCaretPositionStyle() {
  const selection = window.getSelection();

  // Unlikely, but in the case there is no selection, return empty styles so
  // as to avoid a thrown error by `Selection#getRangeAt` on invalid index.
  if (0 === selection.rangeCount) {
    return {};
  }

  // Get position relative viewport.
  const rect = getRectangleFromRange(selection.getRangeAt(0));

  if (! rect) {
    return {};
  }

  let top = rect.top + rect.height;
  let left = rect.left + (rect.width / 2);

  // Offset by positioned parent, if one exists.
  const offsetParent = getOffsetParent(selection.anchorNode);
  if (offsetParent) {
    const parentRect = offsetParent.getBoundingClientRect();
    top -= parentRect.top;
    left -= parentRect.left;
  }

  return { top, left };
}

/**
 * Component which renders itself positioned under the current caret selection.
 * The position is calculated at the time of the component being mounted, so it
 * should only be mounted after the desired selection has been made.
 *
 * @type {WPComponent}
 */
class PositionedAtSelection extends Component {
  static propTypes = {
    children: PropTypes.oneOfType([
      PropTypes.arrayOf(PropTypes.node),
      PropTypes.node,
    ]).isRequired,
  };

  state = {
    style: getCurrentCaretPositionStyle(),
  };

  render() {
    const { children } = this.props;
    const { style } = this.state;

    return (
      <div
        className="editor-format-toolbar__selection-position"
        style={style}
      >
        { children }
      </div>
    );
  }
}

export default PositionedAtSelection;
