import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';

const BylineFreeform = (props) => {
  const {
    addFreeformlabel,
    addFreeformPlaceholder,
    addFreeformButtonLabel,
    onUpdate,
  } = props;

  const [value, setValue] = useState('');

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
    <div
      className="freeform-controls components-base-control__field"
      style={{ marginTop: 15 }}
    >
      <label
        className="components-base-control__label"
        htmlFor="byline_freeform"
      >
        {addFreeformlabel}
      </label>
      <div className="freeformInputGrp">
        <input
          className="components-text-control__input"
          id="byline_freeform"
          name="byline_freeform"
          onChange={(e) => {
            setValue(e.target.value);
          }}
          placeholder={addFreeformPlaceholder}
          type="text"
          value={value}
        />
        <Button
          label={addFreeformButtonLabel}
          className="button"
          disabled={! value}
          isSecondary
          variant="secondary"
          isSmall
          onClick={onSubmit}
          style={{ marginTop: 10 }}
        >
          {addFreeformButtonLabel}
        </Button>
      </div>
    </div>
  );
};

BylineFreeform.propTypes = {
  bylineData: PropTypes.shape({}).isRequired,
  addFreeformlabel: PropTypes.string.isRequired,
  addFreeformPlaceholder: PropTypes.string.isRequired,
  addFreeformButtonLabel: PropTypes.string.isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
