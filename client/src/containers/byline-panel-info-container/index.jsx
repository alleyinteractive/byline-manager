// External dependencies.
import { Fragment } from '@wordpress/element';

// Internal dependencies.
import BylineManagerPanelInfo from './provider';

const BylineManagerPanelInfoProvider = () => (
  <BylineManagerPanelInfo.Slot>
    {(fills) => (
      <Fragment>
        {fills}
      </Fragment>
    )}
  </BylineManagerPanelInfo.Slot>
);

export {
  BylineManagerPanelInfoProvider,
  BylineManagerPanelInfo,
};
