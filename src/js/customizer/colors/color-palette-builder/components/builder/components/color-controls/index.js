import React from 'react';
import { SketchPicker } from 'react-color';
const { useEffect, useState, useRef } = wp.element;
import useOutsideClick from '../../../../../../utils/use-outside-click'

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
    const newColor = {
      label: 'Dark',
      value: '#111111'
    };
    newColors.splice( groupIndex + 1, 0, [ newColor ] );
    setColors( newColors );
  };

  const addNewColorToGroup = ( groupIndex ) => {
    const newColors = colors.slice();
    const newColor = {
      label: 'Interpolated Color',
      value: '#111111'
    };
    newColors[groupIndex].push( newColor );
    setColors( newColors );
  }

  const deleteColor = ( groupIndex, index ) => {
    const newColors = colors.slice();
    newColors[groupIndex].splice( index, 1 );

    if ( ! newColors[groupIndex].length ) {
      newColors.splice( groupIndex, 1 );
    }

    setColors( newColors );
  }

  const updateColor = ( groupIndex, index, newValue ) => {
    const newColors = colors.slice();
    newColors[groupIndex][index] = Object.assign( {}, newColors[groupIndex][index], newValue );
    setColors( newColors );
  }

  return (
    <div className="c-palette-builder">
      <div className="sm-label">Brand Colors</div>
      <div className="c-palette-builder__source-list">
        {
          colors.map( ( colorGroup, groupIndex ) => {
            return (
              <div className="c-palette-builder__source-group">
                {
                  colorGroup.map( ( color, index ) => {

                    const actions = [
                      { label: 'Interpolate Color', callback: () => { addNewColorToGroup( groupIndex ) } },
                      { label: 'Add Color', callback: () => { addNewColorGroup( groupIndex ) } },
//                      { label: 'Rename Color', callback: () => {} },
                      { label: 'Remove Color', callback: () => { deleteColor( groupIndex, index ) } },
                    ];

                    return (
                      <PaletteSourceItem
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

const ContextualMenu = ( props ) => {

  const { actions } = props;
  const [ showMenu, setShowMenu ] = useState( false );

  const ref = useRef( null );

  useOutsideClick( ref, () => {
    setShowMenu( false );
  } );

  return (
    <div ref={ ref } className={ `c-contextual-menu c-contextual-menu--${ showMenu ? 'visible' : 'hidden' }`}>
      <button className="c-contextual-menu__toggle" onClick={ (e) => {
        e.preventDefault();
        setShowMenu( ! showMenu ) } }>
        <span>Toggle Menu</span>
      </button>
      <div className="c-contextual-menu__list">
        { actions.map( ( { label, callback } ) => {
          return <div className="c-contextual-menu__list-item" onClick={ (e) => {
            e.preventDefault();
            setShowMenu(false);
            callback();
          } }>{ label }</div>;
        } ) }
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
