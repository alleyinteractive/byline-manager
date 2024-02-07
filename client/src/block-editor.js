// External dependencies.
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

// Internal dependencies.
import {
  BylineManagerPanelInfoProvider,
  BylineManagerPanelInfo,
  BylineSlotContainer,
} from './containers';

// Get and register our store.
import store from './store';

// Styles.
import './styles/styles.scss';

function BylineManagerSlotFill() {
  return (
    <BylineManagerPanelInfo>
      <p>
        <strong>{__('Byline', 'byline-manager')}</strong>
      </p>
      <BylineSlotContainer store={store} />
    </BylineManagerPanelInfo>
  );
}

// Register our SlotFill provider.
registerPlugin('byline-manager-panel-info-provider', { render: BylineManagerPanelInfoProvider });

// Register core slot fill.
registerPlugin('byline-manager', { render: BylineManagerSlotFill });
