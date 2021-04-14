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

    if ( ! sourceSetting ) {
      return;
    }

    sourceSetting.bind( changeListener );

    return () => {
      sourceSetting.unbind( changeListener );
    }
  }, [] );

  useEffect(() => {

    if ( ! variationSetting ) {
      return;
    }

    variationSetting.bind( changeListener );

    return () => {
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

  const [overrideBack, setOverrideBack] = useState( false );

  useEffect( () => {

    const callback = ( isExpanded ) => {

      if ( ! isExpanded && overrideBack ) {
        wp.customize.section( 'sm_color_palettes_section', ( colorPalettesSection ) => {
          colorPalettesSection.focus();
        } );
        setOverrideBack( false );
      }
    }

    const colorUsageSection = wp.customize.section( 'sm_color_usage_section' );

    if ( ! colorUsageSection ) {
      return;
    }

    colorUsageSection.expanded.bind( callback );

    return () => {
      colorUsageSection.expanded.unbind( callback );
    }

  }, [ overrideBack ] )

  return (
    <ConfigContext.Provider value={ { config, setConfig, resetActivePreset } }>
      <div className="sm-group">
        <div className="sm-panel-toggle" onClick={ () => {
          wp.customize.section( 'sm_color_usage_section', ( colorUsageSection ) => {
            setOverrideBack( true );
            colorUsageSection.focus();
          } );
        } }>
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
