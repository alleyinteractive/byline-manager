/* globals React */
import PropTypes from 'prop-types';

const BylineFreeform = (props) => {
  const {
    onUpdate,
  } = props;

  const [value, setValue] = React.useState('');

  const generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  const onSubmit = (event) => {
    event.preventDefault();

    onUpdate({
      id: generateKey('text'),
      name: value,
    });
    setValue('');
  };

  return (
    <div className="freeform-controls">
      <label htmlFor="byline_freeform">
        {window.bylineData.addFreeformlabel}
        <div className="freeformInputGrp">
          <input
            type="text"
            placeholder={window.bylineData.addFreeformPlaceholder}
            name="byline_freeform"
            id="byline_freeform"
            value={value}
            onChange={(e) => {
              setValue(e.target.value);
            }}
          />
          <button
            aria-label={window.bylineData.addFreeformButtonLabel}
            className="button"
            disabled={! value}
            onClick={onSubmit}
          >
            {window.bylineData.addFreeformButtonLabel}
          </button>
        </div>
      </label>
    </div>
  );
};

BylineFreeform.propTypes = {
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
