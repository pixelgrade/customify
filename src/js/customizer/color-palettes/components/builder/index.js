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
  const sourceSetting = wp.customize( sourceSettingID );

  const colorSpaceSetting = wp.customize( 'sm_color_space' );
  const colorInterpolationSetting = wp.customize( 'sm_color_interpolation' );
  const bezierInterpolationSetting = wp.customize( 'sm_bezier_interpolation' );
  const useSourcesSetting = wp.customize( 'sm_use_color_sources' );

  const outputSetting = wp.customize( outputSettingID );
  const [ colors, setColors ] = useState( getColorsFromInputValue( sourceSetting() ) );
  const [ attributes, updateAttributes ] = useState( {
    correctLightness: true,
    useSources: useSourcesSetting(),
    mode: colorSpaceSetting(),
    colorInterpolation: colorInterpolationSetting(),
    bezierInterpolation: bezierInterpolationSetting(),
  } );

  const setAttributes = ( newAttributes ) => {
    updateAttributes( Object.assign( {}, attributes, newAttributes ) );
  }

  const changeListener = () => {
    setColors( getColorsFromInputValue( sourceSetting() ) );
    setAttributes( {
      useSources: useSourcesSetting(),
      mode: colorSpaceSetting(),
      colorInterpolation: colorInterpolationSetting(),
      bezierInterpolation: bezierInterpolationSetting(),
    } );
  };

  useEffect(() => {
    // Attach the listeners on component mount.
    sourceSetting.bind( changeListener );
    useSourcesSetting.bind( changeListener );
    colorSpaceSetting.bind( changeListener );
    colorInterpolationSetting.bind( changeListener );
    bezierInterpolationSetting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      sourceSetting.unbind( changeListener );
      useSourcesSetting.unbind( changeListener );
      colorSpaceSetting.unbind( changeListener );
      colorInterpolationSetting.unbind( changeListener );
      bezierInterpolationSetting.unbind( changeListener );
    }
  }, []);

  useEffect( () => {
    sourceSetting.set( getValueFromColors( colors ) );
  }, [ colors ] );

  useEffect( () => {
    const palettes = getPalettesFromColors( colors, attributes );

    if ( typeof outputSetting !== "undefined" ) {
      outputSetting.set( JSON.stringify( palettes ) );
    }
  }, [ colors, attributes ] );

  const palettes = getPalettesFromColors( colors, attributes );
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
          <div className={ `palette-preview-set` }>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-bg-color-${ colorIndex })` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-fg1-color-${ colorIndex })` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-accent-color-${ colorIndex })` } }></div> ) }
            </div>
          </div>
        )
      } ) }
    </div>
  );
}

const ParametersControls = ( props ) => {
  const { attributes, setAttributes } = props;
  const { colorInterpolation, bezierInterpolation } = attributes;

  const options = [
    { label: 'RGB', value: 'rgb' },
    { label: 'LAB', value: 'lab' },
    { label: 'LRGB', value: 'lrgb' },
    { label: 'HSL', value: 'hsl' },
    { label: 'LCH', value: 'lch' },
  ]

  return (
    <div>
      <select onChange={ event => setAttributes( { mode: event.target.value } ) } value={ attributes.mode }>
        { options.map( option => <option value={ option.value }>{ option.label }</option> ) }
      </select>
      <div>
        <label
          defaultChecked={ colorInterpolation }
          onChange={ () => setAttributes( { colorInterpolation: ! colorInterpolation } ) }>
          <input type="checkbox" /> Interpolate colors
        </label>
      </div>
      {
        colorInterpolation &&
        <div>
          <label
            defaultChecked={ bezierInterpolation }
            onChange={ () => setAttributes( { bezierInterpolation: ! bezierInterpolation } ) }>
            <input type="checkbox" /> Flatten palette
          </label>
        </div>
      }
    </div>
  )
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

export {
  initializePaletteBuilder,
  getCSSFromPalettes,
}
