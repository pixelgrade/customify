import chroma from 'chroma-js';
import React, { useState } from 'react';
import { useDebouncedCallback } from 'use-debounce';
import { HexColorPicker } from "react-colorful";
import { useDidUpdateEffect } from "../../../utils";


export const ColorPicker = ( props ) => {

  const {
    hex,
    onChange,
    isOpen
  } = props;

  const [ color, setColor ] = useState( hex );
  const [ hexValue, setHexValue ] = useState( hex );

  const debouncedOnChange = useDebouncedCallback( onChange, 200 );

  useDidUpdateEffect( () => {
    debouncedOnChange( color );
  }, [ color ] );

  return (
    <div className={ `c-palette-builder__source-item-color ${ isOpen ? 'c-palette-builder__source-item-color--active' : '' }` }>
      <div className="c-palette-builder__source-item-preview" style={ { color: color } } />
      <div className="c-palette-builder__source-item-picker" onClick={ event => { event.stopPropagation() } }>
        <HexColorPicker color={ color } onChange={ newColor => {
          setHexValue( newColor );
          setColor( newColor );
        } } />
        <input type="text" value={ hexValue } onChange={ ( e ) => {
          const value = e.target.value;

          setHexValue( value );

          if ( chroma.valid( value ) && chroma( value ).alpha() === 1 ) {
            setColor( chroma( value ).hex() );
          }
        } } />
      </div>
    </div>
  )
}
