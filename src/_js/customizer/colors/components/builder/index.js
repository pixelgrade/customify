import React, { useCallback, useEffect, useState, useRef } from 'react';

import { SourceColors } from "../source-colors";
import ConfigContext from "../../context";
import DropZone from "../dropzone";
import PresetsList from '../palette-list';
import Blinds from '../blinds';

import {
  getColorsFromInputValue,
  getPalettesFromColors,
  getCSSFromPalettes,
  getValueFromColors,
} from "./utils";

export { Builder }
export * from './utils';

function useTraceUpdate(props) {
  const prev = useRef(props);
  useEffect(() => {
    const changedProps = Object.entries(props).reduce((ps, [k, v]) => {
      if (prev.current[k] !== v) {
        ps[k] = [prev.current[k], v];
      }
      return ps;
    }, {});
    if (Object.keys(changedProps).length > 0) {
      console.log('Changed props:', changedProps);
    }
    prev.current = props;
  });
}


const Builder = ( props ) => {
  useTraceUpdate( props );

  const { sourceSettingID, outputSettingID } = props;

  const sourceSetting = wp.customize( sourceSettingID );
  const variationSetting = wp.customize( 'sm_site_color_variation' );

  const [ config, setConfig ] = useState( getColorsFromInputValue( sourceSetting() ) );
  const [ palettes, setPalettes ] = useState( [] );
  const [ CSSOutput, setCSSOutput ] = useState( '' );

  const [ activePreset, setActivePreset ] = useState( null );
  const resetActivePreset = useCallback( () => { setActivePreset( null ) }, [] )

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
    setPalettes( getPalettesFromColors( config ) );
  }, [ config ] );

  useEffect( () => {
    wp.customize( outputSettingID, setting => {
      setting.set( JSON.stringify( palettes ) );
    } );
  }, [ palettes ] );

  useEffect( () => {
    setCSSOutput( getCSSFromPalettes( palettes ) );
  }, [ palettes ] );

  return (
    <ConfigContext.Provider value={ { config, setConfig, resetActivePreset } }>
      <div className="sm-group">
        <div className="sm-panel-toggle">
          Customize colors usage
        </div>
      </div>
      <div className="sm-group">
        <div className="sm-group__body">
          <Control label={ 'Brand Colors' }>
            <SourceColors onChange={ () => { setActivePreset( null ) } } />
            <style>{ CSSOutput }</style>
          </Control>
        </div>
        <div className="sm-panel-toggle">
          Fine tune generated palette
        </div>
      </div>
      <div className="sm-group">
        <Blinds title={ 'Explore colors' }>
          <PresetsList active={ activePreset } onChange={ ( preset ) => {
            setConfig( preset.config );
            setActivePreset( preset.uid );
          } } />
        </Blinds>
        <Blinds title={ 'Extract from Image' }>
          <DropZone />
        </Blinds>
        <div className="sm-panel-toggle">
          My Palettes
          <div className="sm-label">Coming Soon</div>
        </div>
      </div>
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
