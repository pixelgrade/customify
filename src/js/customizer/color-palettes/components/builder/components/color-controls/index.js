import './style.scss';

const ColorControls = ( props ) => {
  const { colors, setColors } = props;

  return (
    <div className="c-palette-builder">
      <div className="c-palette-builder__source-list">
        {
          colors.map( ( color, index ) => {
            return (
              <div className="c-palette-builder__source-item">
                <input className="c-palette-builder__source-item-label" type="text" value={color.label} onChange={e => {
                  const newColors = colors.slice();
                  newColors[index] = {
                    label: e.target.value,
                    value: color.value,
                  };
                  setColors( newColors );
                }}/>
                <input className="c-palette-builder__source-item-color" type="color" value={color.value} onChange={e => {
                  const newColors = colors.slice();
                  newColors[index] = {
                    label: color.label,
                    value: e.target.value
                  };
                  setColors( newColors );
                }}/>
                <button className="c-palette-builder__source-item-delete" onClick={(e) => {
                  e.preventDefault();
                  const newColors = colors.slice();
                  newColors.splice( index, 1 );
                  setColors( newColors );
                }}>Delete
                </button>
              </div>
            );
          } )
        }
      </div>
      {
        colors.length < 3 &&
        <button className="c-palette-builder__add" onClick={(e) => {
          e.preventDefault();
          setColors( [ ...colors, {
            label: 'Dark',
            value: '#111111'
          } ] )
        } }>Add Color
        </button>
      }
    </div>

  )
}

export default ColorControls;
