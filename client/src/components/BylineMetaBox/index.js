/*
 * Byline Meta Box.
 */

import React, { Component } from 'react';
import BylineProfiles from './BylineProfiles';
import BylineOverride from './BylineOverride';

class BylineMetaBox extends Component {
  constructor(props) {
    super(props);

    this.metaBoxData = window.bylineData.bylineMetaBox || {};

    this.state = {
      override: 'override' === this.metaBoxData.source,
    };
  }

  render() {
    return (
      <div className="byline-list byline-manager-meta-box">
        <input type="hidden" name="byline_source" value="profiles" />
        <label htmlFor="byline_source" className="byline-override-option">
          <input
            type="checkbox"
            name="byline_source"
            id="byline_source"
            value="override"
            checked={this.state.override}
            onChange={() => this.setState({ override: ! this.state.override })}
          />
          Enter byline manually
        </label>
        {
          (
            this.state.override &&
            <BylineOverride override={this.metaBoxData.override} />
          ) ||
          <BylineProfiles profiles={this.metaBoxData.profiles} />
        }
      </div>
    );
  }
}

export default BylineMetaBox;
