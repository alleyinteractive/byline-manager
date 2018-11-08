/*
 * Byline Meta Box.
 */

import React, { Component } from 'react';
import BylineProfiles from './BylineProfiles';

class BylineMetaBox extends Component {
  constructor(props) {
    super(props);

    this.metaBoxData = window.bylineData.bylineMetaBox || {};
  }

  render() {
    const profiles = [...this.metaBoxData.profiles, this.metaBoxData.freeforms];
    return (
      <div className="byline-list byline-manager-meta-box">
        <input type="hidden" name="byline_source" value="profiles" />
        <BylineProfiles profiles={profiles} />
      </div>
    );
  }
}

export default BylineMetaBox;
