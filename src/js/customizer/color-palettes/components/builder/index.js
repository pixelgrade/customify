const {
  useCallback,
  useEffect,
  useState,
} = wp.element;

import ColorControls from "./components/color-controls";

import {
  getPalettesFromColors,
  getCSSFromPalettes,
  getShiftedArray,
  getSourceIndex,
} from "./utils";

const getColorsFromInputValue = ( value ) => {
  let colors;

  try {
    colors = JSON.parse( value );
  } catch( e ) {
    colors = [];
  }

  return colors;
}

const getValueFromColors = ( colors ) => {
  return JSON.stringify( colors );
}

const Builder = ( props ) => {
  const { sourceSettingID, outputSettingID } = props;
  const variationSetting = wp.customize( 'sm_site_color_variation' );
  const sourceSetting = wp.customize( sourceSettingID );
  const outputSetting = wp.customize( outputSettingID );
  const [ colors, setColors ] = useState( getColorsFromInputValue( sourceSetting() ) );

  const changeListener = useCallback( value => {
    const newColors = getColorsFromInputValue( sourceSetting() );
    const palettes = getPalettesFromColors( newColors );

    setColors( newColors );

    if ( typeof outputSetting !== "undefined" ) {
      outputSetting.set( JSON.stringify( palettes ) );
    }
  }, [ colors ] );

  const variationChangeListener = useCallback( value => {

  }, [] )

  useEffect(() => {
    // Attach the listeners on component mount.
    sourceSetting.bind( changeListener );
    variationSetting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      sourceSetting.unbind( changeListener );
      variationSetting.unbind( changeListener );
    }
  }, []);

  useEffect( () => {
    sourceSetting.set( getValueFromColors( colors ) );
  }, [ colors ] );

  const palettes = getPalettesFromColors( colors );
  const isDark = window?.myApi?.isDark ? window.myApi.isDark() : false;

  return (
    <div className={ isDark ? 'is-dark' : '' }>
      <ColorControls colors={ colors } setColors={ setColors } />
      <style>
        { getCSSFromPalettes( palettes ) }
      </style>
      { palettes.map( ( palette, index ) => {
        const { colors, id } = palette;

        return (
          <div className={ `palette-preview-set sm-palette-${ id }` }>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-current-background-color)` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-current-dark-color)` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-current-accent-color)` } }></div> ) }
            </div>
          </div>
        )
      } ) }
    </div>
  );
}

const initializePaletteBuilder = ( sourceSettingID, outputSettingID ) => {
  const containerID = `customize-control-${ sourceSettingID }_control`;
  const container = document.getElementById( containerID );
  const target = document.createElement( 'DIV' );

  if ( typeof container === "undefined" ) {
    return;
  }

  container.children.forEach( child => {
    child.style.display = 'none';
  } );

  container.insertBefore( target, container.firstChild );
  wp.element.render( <Builder sourceSettingID={ sourceSettingID } outputSettingID={ outputSettingID }/>, target );
}

const getCSSFromInputValue = ( value ) => {
  const colors = getColorsFromInputValue( value );
  const palettes = getPalettesFromColors( colors );

  return getCSSFromPalettes( palettes );
}

export {
  initializePaletteBuilder,
  getCSSFromInputValue,
  getCSSFromPalettes,
}
