// External dependencies.
import { Fragment } from '@wordpress/element';

// Internal dependencies.
import BylineManagerPanelInfo from '../panel/index';

const BylineManagerPanelInfoProvider = () => (
  <BylineManagerPanelInfo.Slot>
    {(fills) => (
      <Fragment>
        {fills}
      </Fragment>
    )}
  </BylineManagerPanelInfo.Slot>
);

export default BylineManagerPanelInfoProvider;
