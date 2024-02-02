// Internal dependencies.
import BylineManagerPanelInfo from '../panel/index';

function BylineManagerPanelInfoProvider() {
  return (
    <BylineManagerPanelInfo.Slot>
      {(fills) => (
        { fills }
      )}
    </BylineManagerPanelInfo.Slot>
  );
}

export default BylineManagerPanelInfoProvider;
