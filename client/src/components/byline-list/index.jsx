// External dependencies.
import { SortableContainer } from 'react-sortable-hoc';
import { __experimentalItemGroup as ItemGroup } from '@wordpress/components';

// Internal dependencies.
import BylineListItem from '../byline-list-item';

const BylineList = SortableContainer(({
  profiles,
  removeItem,
  removeAuthorLabel,
}) => (
  <ItemGroup style={{ margin: '15px 0 0' }}>
    {profiles.map((profile, index) => (
      <BylineListItem
        key={`item-${profile.id}`}
        index={index}
        count={index}
        bylineId={profile.byline_id || 0}
        name={profile.name || ''}
        image={profile.image || ''}
        removeItem={() => removeItem(profile.id)}
        removeAuthorLabel={removeAuthorLabel}
      />
    ))}
  </ItemGroup>
));

export default BylineList;
