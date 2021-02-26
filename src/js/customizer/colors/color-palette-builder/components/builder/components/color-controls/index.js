import React from 'react';
import { SketchPicker } from 'react-color';
const { useState, useRef } = wp.element;
import useOutsideClick from '../../../../../../utils/use-outside-click'

import './style.scss';

const ColorControls = ( props ) => {
  const { colors, setColors } = props;

  return (
    <div className="c-palette-builder">
      <div className="c-palette-builder__header">
        <div className="c-palette-builder__label">
          Brand Colors
        </div>
        <button className="c-palette-builder__button c-palette-builder__add" onClick={(e) => {
          e.preventDefault();
          setColors( [ ...colors, {
            label: 'Dark',
            value: '#111111'
          } ] )
        } }>New Color
        </button>
      </div>
      <div className="c-palette-builder__source-list">
        {
          colors.map( ( color, index ) => {
            return (
              <div className="c-palette-builder__source-item">
                <ColorPicker hex={ color.value } onChange={ hex => {
                  const newColors = colors.slice();
                  newColors[index] = {
                    label: color.label,
                    value: hex
                  };
                  setColors( newColors );
                } } />
                <input className="c-palette-builder__source-item-label" type="text" value={color.label} onChange={e => {
                  const newColors = colors.slice();
                  newColors[index] = {
                    label: e.target.value,
                    value: color.value,
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
    </div>
  )
}

const ColorPicker = ( props ) => {

  const {
    hex,
    onChange
  } = props;

  const [ color, setColor ] = useState( hex );
  const [ showPicker, setShowPicker ] = useState( false );
  const pickerRef = useRef( null );

  useOutsideClick( pickerRef, () => {
    setShowPicker( false );
  } );

  return (
    <div className={ `c-palette-builder__source-item-picker ${ showPicker ? 'active' : '' }` } ref={ pickerRef }>
      <div className="c-palette-builder__source-item-preview" style={ { color: hex } } onClick={ () => { setShowPicker( ! showPicker ) } }></div>
      { showPicker && <SketchPicker
        color={ color }
        onChange={ newColor => {
          setColor( newColor.hex );
        } }
        onChangeComplete={ newColor => {
          onChange( newColor.hex );
        } }
      /> }
    </div>
  )
}

export default ColorControls;
