import React from 'react';
import { SketchPicker } from 'react-color';
import { ContextualMenu } from "../../../contextual-menu";
import useOutsideClick from '../../../../../../utils/use-outside-click'

const { useEffect, useState, useRef } = wp.element;

import './style.scss';

const ColorControls = ( props ) => {

  const { colors, setColors } = props;

  useEffect( () => {
    if ( ! colors.length ) {
      addNewColorGroup();
    }
  }, [ colors ] );

  const addNewColorGroup = ( groupIndex = 0 ) => {
    const newColors = colors.slice();
    const newGroup = {
      uid: `color_group_${ new Date().getTime() }`,
      sources: [ {
        uid: `color_${ new Date().getTime() }`,
        label: 'Dark',
        value: '#111111'
      } ]
    }
    newColors.splice( groupIndex + 1, 0, newGroup );
    setColors( newColors );
  };

  const addNewColorToGroup = ( groupIndex ) => {
    const newColors = colors.slice();
    const newColor = {
      uid: `color_${ new Date().getTime() }`,
      label: 'Interpolated Color',
      value: '#111111'
    };
    newColors[groupIndex].sources.push( newColor );
    setColors( newColors );
  }

  const deleteColor = ( groupIndex, index ) => {
    const newColors = colors.slice();
    newColors[groupIndex].sources.splice( index, 1 );

    if ( ! newColors[groupIndex].sources.length ) {
      newColors.splice( groupIndex, 1 );
    }

    setColors( newColors );
  }

  const updateColor = ( groupIndex, index, newValue ) => {
    const newColors = colors.slice();
    newColors[groupIndex].sources[index] = Object.assign( {}, newColors[groupIndex].sources[index], newValue );
    setColors( newColors );
  }

  return (
    <div className="c-palette-builder">
      <div className="sm-label">Brand Colors</div>
      <div className="c-palette-builder__source-list">
        {
          colors.map( ( colorGroup, groupIndex ) => {
            const colors = colorGroup.sources || colorGroup;
            return (
              <div key={ colorGroup.uid } className="c-palette-builder__source-group">
                {
                  colors.map( ( color, index ) => {

                    const actions = [
                      { label: 'Interpolate Color', callback: () => { addNewColorToGroup( groupIndex ) } },
                      { label: 'Add Color', callback: () => { addNewColorGroup( groupIndex ) } },
//                      { label: 'Rename Color', callback: () => {} },
                      { label: 'Remove Color', callback: () => { deleteColor( groupIndex, index ) } },
                    ];

                    return (
                      <PaletteSourceItem
                        key={ color.uid }
                        actions={ actions }
                        color={ color }
                        onChange={ ( newValue ) => { updateColor( groupIndex, index, newValue ) } }
                      />
                    );

                  } )
                }
              </div>
              )
          } )
        }
      </div>
    </div>
  )
}

const PaletteSourceItem = ( props ) => {

  const {
    color,
    onChange,
    actions,
  } = props;

  return (
    <div className="c-palette-builder__source-item">
      <ColorPicker hex={ color.value } onChange={ hex => { onChange( { value: hex } ) } } />
      <input className="c-palette-builder__source-item-label" type="text" value={ color.label } onChange={ e => {
        onChange( { label: e.target.value } ) }
      } />
      <ContextualMenu actions={ actions } />
    </div>
  );
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
      <div className="c-palette-builder__source-item-preview" style={ { color: color } } onClick={ () => { setShowPicker( ! showPicker ) } }></div>
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
