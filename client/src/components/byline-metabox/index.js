/*
 * Byline Meta Box.
 */

import React, { Component } from 'react';
import BylineProfiles from '../byline-profiles.js';

class BylineMetaBox extends Component {
  constructor(props) {
    super(props);

    this.metaBoxData = window.bylineData.bylineMetaBox || {};
  }

  render() {
    return (
      <div className="byline-list byline-manager-meta-box">
        <input type="hidden" name="byline_source" value="profiles" />
        <BylineProfiles profiles={this.metaBoxData.profiles} />
      </div>
    );
  }
}

export default BylineMetaBox;
