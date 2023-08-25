// Internal dependencies.
import creator from './creator';
import STORE_KEY from './constants';

// Expose the store key.
const store = STORE_KEY;

// Create the default Byline Manager Redux store.
creator(store);

export default store;
