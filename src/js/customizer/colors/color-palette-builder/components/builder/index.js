import { SourceColors } from "../source-colors";
import ConfigContext from "../../context";
import { Preview } from "../preview";

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
  const variationSetting = wp.customize( 'sm_site_color_variation' );

  const [ config, setConfig ] = useState( getColorsFromInputValue( sourceSetting() ) );
  const [ palettes, setPalettes ] = useState( [] );
  const [ CSSOutput, setCSSOutput ] = useState( '' );

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
    setConfig( getColorsFromInputValue( sourceSetting() ) );
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
    sourceSetting.set( getValueFromColors( config ) );
  }, [ config ] );

  useEffect( () => {
    setPalettes( getPalettesFromColors( config, attributes ) );
  }, [ config, attributes ] );

  useEffect( () => {
    wp.customize( outputSettingID, setting => {
      setting.set( JSON.stringify( palettes ) );
    } );
  }, [ palettes ] );

  useEffect( () => {
    setCSSOutput( getCSSFromPalettes( palettes ) );
  }, [ palettes ] );

  return (
      <ConfigContext.Provider value={ { config, setConfig } }>
        <div className="sm-label">Brand Colors</div>
        <SourceColors />
        <div className="sm-label">Color Palette preview</div>
        <Preview palettes={ palettes } />
        <style>{ CSSOutput }</style>
      </ConfigContext.Provider>
  );
}
