/*
 * Byline Override UI.
 */

import React, { Component } from 'react';
import PropTypes from 'prop-types';

class BylineOverride extends Component {
  static propTypes = {
    override: PropTypes.string,
  };

  static defaultProps = {
    override: '',
  };

  constructor(props) {
    super(props);

    this.state = {
      value: props.override,
    };
  }

  render() {
    return (
      <div>
        <label htmlFor="byline_override">
          <span>Enter Byline:</span>
          <input
            type="text"
            name="byline_override"
            id="byline_override"
            value={this.state.value}
            onChange={(e) => this.setState({ value: e.target.value })}
          />
        </label>
      </div>
    );
  }
}

export default BylineOverride;
