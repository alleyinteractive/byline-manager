// External dependencies.
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { createSlotFill, __experimentalDivider as Divider } from '@wordpress/components';
import PropTypes from 'prop-types';

const { Fill, Slot } = createSlotFill('BylineManagerPanelInfo');

const BylineManagerPanelInfo = ({ children }) => (
  <Fill>
    <PluginPostStatusInfo>
      <div style={{ width: '100%' }}>
        <Divider />
        {children}
      </div>
    </PluginPostStatusInfo>
  </Fill>
);

BylineManagerPanelInfo.Slot = Slot;

BylineManagerPanelInfo.propTypes = {
  children: PropTypes.node.isRequired,
};

export default BylineManagerPanelInfo;
