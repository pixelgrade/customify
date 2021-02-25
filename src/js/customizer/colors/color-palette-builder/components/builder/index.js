import './style.scss';
import ColorControls from "./components/color-controls";

import {
  getColorsFromInputValue,
  getPalettesFromColors,
  getCSSFromPalettes,
  getValueFromColors,
} from "./utils";

export { Builder }
export * from './utils';

const { useEffect, useState } = wp.element;

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
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-bg-color-${ colorIndex + 1 })` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-fg1-color-${ colorIndex + 1 })` } }></div> ) }
            </div>
            <div className={ "palette-preview" }>
              { colors.map( ( color, colorIndex ) => <div className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-accent-color-${ colorIndex + 1 })` } }></div> ) }
            </div>
          </div>
        )
      } ) }
    </div>
  );
}
