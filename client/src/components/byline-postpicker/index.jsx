import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useCallback } from '@wordpress/element';
import { PostPicker } from "@alleyinteractive/block-editor-tools";
import PropTypes from 'prop-types';

function BylinePostpicker({
  addAuthorLabel,
  id,
  onUpdate,
  profilesApiUrl,
}) {
  const doProfileSearch = useCallback((profileId) => {
    apiFetch({ url: addQueryArgs(profilesApiUrl, { id: profileId }) })
      .then((rawResults) => {
        onUpdate(rawResults[0]);
      });
  }, [profilesApiUrl, onUpdate]);

  return (
    <>
      <label
        className="components-base-control__label"
        htmlFor={id}
      >
        {addAuthorLabel}
      </label>
      <PostPicker
        allowedTypes={['profile']}
        onUpdate={(profile) => doProfileSearch(profile)}
      />
    </>
  );
}

BylinePostpicker.defaultProps = {
  id: 'profiles_postpicker',
};

BylinePostpicker.propTypes = {
  addAuthorLabel: PropTypes.string.isRequired,
  id: PropTypes.string,
  onUpdate: PropTypes.func.isRequired,
  profilesApiUrl: PropTypes.string.isRequired,
};

export default BylinePostpicker;
