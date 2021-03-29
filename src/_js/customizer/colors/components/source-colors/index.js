import { ColorPicker } from './color-picker';
import { ContextualMenu } from "../contextual-menu";
import useOutsideClick from '../../../utils/use-outside-click';
import ConfigContext from "../../context";

import {
  addNewColorGroup,
  addNewColorToGroup,
  deleteColor,
  updateColor,
} from "./utils";

const { useContext, useEffect, useState, useRef } = wp.element;

import './style.scss';

const SourceColors = () => {
  const { config, setConfig } = useContext( ConfigContext );

  useEffect( () => {

    if ( ! config.length ) {
      setConfig( addNewColorGroup( config ) );
      return;
    }

    if ( ! config.filter( group => { return !! group.sources.length } ).length ) {
      setConfig( [] );
    }

  }, [ config ] );

  return (
    <div className="c-palette-builder__source-list">
      { config.map( ( group, groupIndex ) => (
        <SourceColorsGroup
          key={ group.uid }
          sources={ group.sources }
          index={ groupIndex }
        />
      ) ) }
    </div>
  )
}

const SourceColorsGroup = ( props ) => {
  const { uid, sources } = props;
  const groupIndex = props.index;

  return (
    <div key={ uid } className="c-palette-builder__source-group">
      { sources.map( ( color, index ) => (
        <SourceColorControl
          key={ color.uid }
          groupIndex={ groupIndex }
          index={ index }
          color={ color }
          showPicker={ color.showPicker }
        />
      ) ) }
    </div>
  )
}

const SourceColorControl = ( props ) => {

  const {
    color,
    index,
    groupIndex
  } = props;

  const [ active, setActive ] = useState( false );
  const [ hover, setHover ] = useState( false );
  const [ menuIsOpen, setMenuIsOpen ] = useState( false );
  const [ editable, setEditable ] = useState( false );
  const [ showPicker, setShowPicker ] = useState();

  const { config, setConfig } = useContext( ConfigContext );

  const onChange = ( newValue ) => {
    setConfig( updateColor( config, groupIndex, index, newValue ) )
  };

  const actions = [
    { label: 'Interpolate Color', callback: () => { setConfig( addNewColorToGroup( config, groupIndex, index ) ) } },
    { label: 'Add Color', callback: () => { setConfig( addNewColorGroup( config, groupIndex ) ) } },
    { label: 'Rename Color', callback: () => { setEditable( true ) } },
    { label: 'Remove Color', callback: () => { setConfig( deleteColor( config, groupIndex, index ) ) }, className: 'c-contextual-menu__list-item--danger' },
  ];

  const inputRef = useRef( null );
  const pickerRef = useRef( null );

  useOutsideClick( pickerRef, () => {
    setShowPicker( false );
  } );

  // delay setting showPicker with one render cycle in order to show fadein animation
  useEffect( () => {
    if ( typeof showPicker === "undefined" && typeof props.showPicker !== "undefined" ) {
      setShowPicker( props.showPicker );
    }
  }, [ showPicker ] );

  useEffect( () => {
    setActive( hover || menuIsOpen );
  }, [ hover, menuIsOpen ] )

  useEffect( () => {
    if ( editable ) {
      inputRef.current.focus();
    }
  }, [ editable ] );

  const onLabelBlur = e => {
    setEditable( false );
  };

  return (
    <div
      onMouseEnter={ () => { setHover( true ) } }
      onMouseLeave={ () => { setHover( false ) } }
      onClick={ () => { setShowPicker( ! showPicker ) } }
      ref={ pickerRef }
      className={ `c-palette-builder__source-item ${ active ? 'c-palette-builder__source-item--active' : '' }` }>
      <ColorPicker hex={ color.value } onChange={ hex => { onChange( { value: hex } ) } } isOpen={ showPicker } />
      { ! editable && <div className="c-palette-builder__source-item-label">{ color.label }</div> }
      { editable &&
        <input type="text"
               ref={ inputRef }
               value={ color.label }
               className="c-palette-builder__source-item-label"
               onChange={ e => { onChange( { label: e.target.value } ) } }
               onBlur={ onLabelBlur } />
      }
      <ContextualMenu actions={ actions } onToggle={ setMenuIsOpen } onClick={ ( event ) => {
        event.stopPropagation();
        setShowPicker( false );
      } } />
    </div>
  );
}

export { SourceColors };
