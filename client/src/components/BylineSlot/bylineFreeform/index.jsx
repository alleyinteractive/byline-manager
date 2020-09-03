/* globals React */
import PropTypes from 'prop-types';

const BylineFreeform = (props) => {
  const {
    byline,
    onUpdate,
  } = props;

  const [value, setValue] = React.useState('');

  const onSubmit = (event) => {
    event.preventDefault();

    const newItem = {
      type: 'text',
      atts: {
        text: value,
      },
    };

    onUpdate('byline', {
      profiles: [
        ...byline.profiles,
        newItem,
      ],
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
  byline: PropTypes.shape({
    profiles: PropTypes.array,
  }).isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
