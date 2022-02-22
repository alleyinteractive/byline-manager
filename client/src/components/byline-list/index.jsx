import { SortableContainer } from 'react-sortable-hoc';
import { __experimentalItemGroup as ItemGroup } from '@wordpress/components';

// Components.
import BylineListItem from '../byline-list-item';

export default SortableContainer(({ profiles, removeItem }) => (
  <ItemGroup style={{ margin: '15px 0 0' }}>
    {profiles.map((profile, index) => (
      <BylineListItem
        key={`item-${profile.id}`}
        index={index}
        count={index}
        bylineId={profile.byline_id}
        name={profile.name || ''}
        image={profile.image || ''}
        removeItem={() => removeItem(profile.id)}
      />
    ))}
  </ItemGroup>
));
