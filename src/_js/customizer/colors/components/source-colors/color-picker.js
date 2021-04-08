import React, { useState } from 'react';
import { useDebouncedCallback } from 'use-debounce';
import { HexColorPicker } from "react-colorful";

export const ColorPicker = ( props ) => {

  const {
    hex,
    onChange,
    isOpen
  } = props;

  const [ color, setColor ] = useState( hex );

  const debouncedOnChange = useDebouncedCallback( onChange, 200 );

  return (
    <div className={ `c-palette-builder__source-item-color ${ isOpen ? 'c-palette-builder__source-item-color--active' : '' }` }>
      <div className="c-palette-builder__source-item-preview" style={ { color: hex } } />
      <div className="c-palette-builder__source-item-picker" onClick={ event => { event.stopPropagation() } }>
        <HexColorPicker color={ color } onChange={ newColor => {
          setColor( newColor );
          debouncedOnChange( newColor );
        } } />
      </div>
    </div>
  )
}
