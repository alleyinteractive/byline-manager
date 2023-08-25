// External dependencies.
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

const BylineFreeform = ({
  id,
  addFreeformLabel,
  addFreeformPlaceholder,
  addFreeformButtonLabel,
  onUpdate,
}) => {
  const [textByline, setTextByline] = useState('');

  const generateKey = (pre) => `${pre}-${new Date().getTime()}`;

  const onSubmit = (event) => {
    event.preventDefault();

    onUpdate({
      id: generateKey('text'),
      name: textByline,
    });

    setTextByline('');
  };

  return (
    <div
      className="freeform-controls components-base-control__field"
      style={{ marginTop: 15 }}
    >
      <label
        className="components-base-control__label"
        htmlFor={id}
      >
        {addFreeformLabel}
      </label>
      <div className="freeformInputGrp">
        <input
          className="components-text-control__input"
          id={id}
          name={id}
          onChange={(e) => {
            setTextByline(e.target.value);
          }}
          placeholder={addFreeformPlaceholder}
          type="text"
          value={textByline}
        />
        <Button
          label={addFreeformButtonLabel}
          className="button"
          size="small"
          variant="secondary"
          disabled={! textByline}
          onClick={onSubmit}
        >
          {addFreeformButtonLabel}
        </Button>
      </div>
    </div>
  );
};

BylineFreeform.defaultProps = {
  id: 'byline_freeform',
};

BylineFreeform.propTypes = {
  id: PropTypes.string,
  addFreeformLabel: PropTypes.string.isRequired,
  addFreeformPlaceholder: PropTypes.string.isRequired,
  addFreeformButtonLabel: PropTypes.string.isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
