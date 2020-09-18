import { SortableContainer } from 'react-sortable-hoc';
import BylineListItem from './bylineListItem';

export default SortableContainer(({ profiles, removeItem }) => (
  <ol style={{ margin: 0 }}>
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
  </ol>
));
