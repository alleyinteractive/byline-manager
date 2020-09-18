import { SortableElement } from 'react-sortable-hoc';

export default SortableElement(({
  name,
  image,
  removeItem,
}) => (
  <li className="byline-list-item">
    { image && <img src={image} alt={name} /> }
    <span>{name}</span>
    <button
      aria-label={window.bylineData.removeAuthorLabel}
      onClick={(e) => {
        e.preventDefault();
        removeItem();
      }}
    >
      &times;
    </button>
  </li>
));
