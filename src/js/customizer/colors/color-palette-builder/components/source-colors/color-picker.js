import React from "react";
import { SketchPicker } from "react-color";

const { useState } = wp.element;

export const ColorPicker = ( props ) => {

  const {
    hex,
    onChange,
    isOpen
  } = props;

  const [ color, setColor ] = useState( hex );

  return (
    <div className={ `c-palette-builder__source-item-color ${ isOpen ? 'c-palette-builder__source-item-color--active' : '' }` }>
      <div className="c-palette-builder__source-item-preview" style={ { color: color } } ></div>
      <div className="c-palette-builder__source-item-picker" onClick={ event => { event.stopPropagation() } }>
        <SketchPicker
          color={ color }
          onChange={ newColor => {
            setColor( newColor.hex );
          } }
          onChangeComplete={ newColor => {
            onChange( newColor.hex );
          } }
        />
      </div>
    </div>
  )
}
