// WordPress dependencies.
import { createReduxStore, register } from '@wordpress/data';

// Internal dependencies.
import * as actions from './actions';
import resolveProfiles from './resolvers';
import getProfiles from './selectors';
import reducer from './reducer';
import controls from './controls';

/**
 * Byline Manager store registration.
 *
 * @example
 * ```js
 * import { storeCreator } from 'path/to/byline/manager/store';
 *
 * storeCreator(MY_STORE_CUSTOM_KEY, 'meta_key');
 * ```
 *
 * @param {String} storeKey Redux store key.
 * @param {String} metaKey Meta key.
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
    }
  );

  register(store);
};

export default creator;
