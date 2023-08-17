// External dependencies.
import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

const BylineFreeform = ({
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
        htmlFor="byline_freeform"
      >
        {addFreeformLabel}
      </label>
      <div className="freeformInputGrp">
        <input
          className="components-text-control__input"
          id="byline_freeform"
          name="byline_freeform"
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
          variant="secondary"
          disabled={! textByline}
          isSmall
          onClick={onSubmit}
        >
          {addFreeformButtonLabel}
        </Button>
      </div>
    </div>
  );
};

BylineFreeform.propTypes = {
  addFreeformLabel: PropTypes.string.isRequired,
  addFreeformPlaceholder: PropTypes.string.isRequired,
  addFreeformButtonLabel: PropTypes.string.isRequired,
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
