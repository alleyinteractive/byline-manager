import PropTypes from 'prop-types';

export default {
  id: PropTypes.number,
  byline_id: PropTypes.number,
  name: PropTypes.string,
  image: PropTypes.oneOfType([
    PropTypes.bool,
    PropTypes.string,
  ]),
};
