// WordPress dependencies.
import { createReduxStore, register } from '@wordpress/data';

// Internal dependencies.
import * as actions from './actions';
import resolveProfiles from './resolvers';
import getProfiles from './selectors';
import reducer from './reducer';
import controls from './controls';

/**
 * Byline Manager Redux store registration.
 *
 * @example
 * ```js
 * import storeCreator from 'path/to/byline/manager/creator';
 *
 * storeCreator(MY_STORE_CUSTOM_KEY, 'meta_key');
 * ```
 *
 * @param {string} storeKey Redux store key.
 * @param {string} metaKey Meta key. Default: 'byline'.
 */
const creator = (storeKey, metaKey = 'byline') => {
  const store = createReduxStore(
    storeKey,
    {
      reducer,
      actions,
      selectors: {
        getProfiles,
      },
      controls,
      resolvers: {
        getProfiles: () => resolveProfiles(storeKey, metaKey),
      },
    },
  );

  register(store);
};

export default creator;
