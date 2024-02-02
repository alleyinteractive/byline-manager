// External dependencies.
import { SortableElement } from 'react-sortable-hoc';
import { __experimentalItem as Item, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const BylineListItem = SortableElement(({
  name,
  image,
  removeItem,
  removeAuthorLabel,
}) => (
  <Item className="byline-list-item">
    { image ? <img src={image} alt={name} /> : null }
    <span>{name}</span>
    <Button
      label={removeAuthorLabel}
      isDestructive
      variant="secondary"
      size="small"
      onClick={(e) => {
        e.preventDefault();
        removeItem();
      }}
    >
      {__('Remove', 'byline-manager')}
    </Button>
  </Item>
));

export default BylineListItem;
