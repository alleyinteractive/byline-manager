// External dependencies.
import { Fragment } from '@wordpress/element';

// Internal dependencies.
import BylineManagerPanelInfo from '../panel/index';

function BylineManagerPanelInfoProvider() {
  return (
    <BylineManagerPanelInfo.Slot>
      {(fills) => (
        // eslint-disable-next-line react/jsx-no-useless-fragment
        <Fragment>
          {fills}
        </Fragment>
      )}
    </BylineManagerPanelInfo.Slot>
  );
}

export default BylineManagerPanelInfoProvider;
