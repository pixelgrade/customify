import React, { useEffect, useState } from 'react';

import { SourceColors } from "../source-colors";
import ConfigContext from "../../context";
import DropZone, { myWorker } from "../dropzone";
import PaletteList from '../palette-list';

import {
  getColorsFromInputValue,
  getPalettesFromColors,
  getCSSFromPalettes,
  getValueFromColors,
} from "./utils";
import chroma from "chroma-js";

export { Builder }
export * from './utils';

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

  const [ colors, setColors ] = useState( [] );

  useEffect( () => {
    myWorker.onmessage = function( event ) {
      const type = event.data.type;

      if ( 'palette' === type ) {
        const colors = event.data.colors;
        const hexColors = colors.map( rgb => chroma( rgb ).hex() );

        hexColors.forEach( color1 => {
          hexColors.forEach( ( color2, index ) => {
            if ( color1 !== color2 && chroma.distance( color1, color2 ) < 30 ) {
//              hexColors.splice( index, 1 );
            }
          } );
        } );

        setColors( hexColors );
      }
    };

    return () => {
      delete myWorker.onmessage;
    };

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
      <Control label={ 'Brand Colors' }>
        <SourceColors />
      </Control>
      <style>{ CSSOutput }</style>
      <Control label={ 'Explore colors' }>
        <PaletteList />
      </Control>
      <Control label={ 'Extract from Image' }>
        <DropZone />
        <div className="c-palette-builder__source-list">
          <div className="c-palette-builder__source-group">
            { colors.map( color => {
              return (
                <div className="c-palette-builder__source-item">
                  <div className="c-palette-builder__source-item-color">
                    <div className="c-palette-builder__source-item-preview" style={ { color: color } } />
                  </div>
                  <div className="c-palette-builder__source-item-label">{ color }</div>
                </div>
              )
            } ) }
          </div>
        </div>
      </Control>
    </ConfigContext.Provider>
  );
}

const Control = ( props ) => {
  const { label, children } = props;

  return (
    <div className="sm-control">
      { label &&
        <div className="sm-control__header">
          <div className="sm-control__label">{ label }</div>
        </div>
      }
      { children && <div className="sm-control__body">{ children }</div> }
    </div>
  )
}
