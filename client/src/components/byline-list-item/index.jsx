import { SortableElement } from 'react-sortable-hoc';
import { __experimentalItem as Item, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default SortableElement(({
  name,
  image,
  removeItem,
  removeAuthorLabel,
}) => (
  <Item className="byline-list-item">
    { image && <img src={image} alt={name} /> }
    <span>{name}</span>
    <Button
      label={removeAuthorLabel}
      isLink
      isDestructive
      variant="secondary"
      isSmall
      onClick={(e) => {
        e.preventDefault();
        removeItem();
      }}
    >
      {__('Remove', 'byline-manager')}
    </Button>
  </Item>
));
