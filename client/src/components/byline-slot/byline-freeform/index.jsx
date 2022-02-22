import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@wordpress/components';

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
    <div
      className="freeform-controls components-base-control__field"
      style={{ marginTop: 15 }}
    >
      <label
        className="components-base-control__label"
        htmlFor="byline_freeform"
      >
        {window.bylineData.addFreeformlabel}
      </label>
      <div className="freeformInputGrp">
        <input
          className="components-text-control__input"
          id="byline_freeform"
          name="byline_freeform"
          onChange={(e) => {
            setValue(e.target.value);
          }}
          placeholder={window.bylineData.addFreeformPlaceholder}
          type="text"
          value={value}
        />
        <Button
          label={window.bylineData.addFreeformButtonLabel}
          className="button"
          disabled={! value}
          isSecondary
          variant="secondary"
          isSmall
          onClick={onSubmit}
          style={{ marginTop: 10 }}
        >
          {window.bylineData.addFreeformButtonLabel}
        </Button>
      </div>
    </div>
  );
};

BylineFreeform.propTypes = {
  onUpdate: PropTypes.func.isRequired,
};

export default BylineFreeform;
