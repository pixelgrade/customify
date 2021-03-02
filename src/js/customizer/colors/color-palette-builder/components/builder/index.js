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
  const outputSetting = wp.customize( outputSettingID );
  const variationSetting = wp.customize( 'sm_site_color_variation' );

  const [ colors, setColors ] = useState( getColorsFromInputValue( sourceSetting() ) );
  const [ attributes, updateAttributes ] = useState( {
    correctLightness: true,
    useSources: true,
    mode: 'lch',
    bezierInterpolation: false,
  } );

  const setAttributes = ( newAttributes ) => {
    updateAttributes( Object.assign( {}, attributes, newAttributes ) );
  }

  const changeListener = () => {
    setColors( getColorsFromInputValue( sourceSetting() ) );
  };

  useEffect(() => {
    // Attach the listeners on component mount.
    sourceSetting.bind( changeListener );
    variationSetting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      sourceSetting.unbind( changeListener );
      variationSetting.unbind( changeListener );
    }

  }, [] );

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
      <Preview palettes={ palettes } />
    </div>
  );
}

const Preview = ( props ) => {
  return (
      <div className="palette-preview">
        <div className="sm-label">Color Palette preview</div>
        <PalettesPreview { ...props } />
      </div>
  );
}
const PalettesPreview = ( props ) => {

  const {
    palettes
  } = props;

  return palettes.map( ( palette, index ) => {
    const { colors, id } = palette;

    return (
      <div className={ `palette-preview-set` }>
        <div className="palette-preview-set-header">
          <div className="palette-preview-source">
            {
              palette.source.map( ( source, index ) => {
                return <div key={ index } className="palette-preview-source-color" style={ { color: source } }></div>
              } )
            }
          </div>
          <div className="palette-preview-label">{ `${ palette.label } color palette` }</div>
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-bg-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-fg1-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-accent-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
      </div>
    )
  } )
}
