import ReactDOM from "react-dom";
import React, { useEffect } from "react";

import { debounce } from '../../utils';
import * as globalService from "../global-service";
import { getBackArray, setBackArray, addToBackArray } from "../global-service";
import colorizeElementsIcon from "../svg/colorize-elements.svg";

import { initializePaletteBuilder } from './color-palette-builder';
import initializeColorPalettesPreview from './color-palettes-preview';
import { moveConnectedFields } from './utils';

const darkToColorSliderControls = [
  'sm_dark_color_switch_slider',
  'sm_dark_color_select_slider',
];

const masterSettingIDs = [
  'sm_text_color_switch_master',
  'sm_accent_color_switch_master',
  'sm_text_color_select_master',
  'sm_accent_color_select_master',
];

export const initializeColors = () => {

  initializePaletteBuilder( 'sm_advanced_palette_source', 'sm_advanced_palette_output' );

  wp.customize( 'sm_coloration_level', setting => {
    setting.bind( applyColorationValueToFields );
  } );

  darkToColorSliderControls.forEach( settingID => {
    wp.customize( settingID, setting => {
      setting.bind( debounce( newValue => {
        reloadConnectedFields();
        applyMasterSettingsValues();
      }, 30 ) )
    } )
  } )

  initializeColorizeElementsButton();
  initializeColorPalettesPreview();

  reloadConnectedFields();
}

const ColorizeElementsButton = ( props ) => {

  const targetSectionID = `${ customify.config.options_name }[colors_section]`;

  useEffect( () => {

    const callback = ( isExpanded ) => {

      if ( ! isExpanded ) {
        const backArray = getBackArray();
        const targetSectionID = backArray.pop();

        if ( targetSectionID ) {
          wp.customize.section( targetSectionID, ( targetSection ) => {
            targetSection.focus();
          } );
        }
      }
    }

    const targetSection = wp.customize.section( targetSectionID );

    if ( ! targetSection ) {
      return;
    }

    targetSection.expanded.bind( callback );

    return () => {
      targetSection.expanded.unbind( callback );
    }

  }, [] );

  return (
    <div className="sm-group" style={ { marginTop: 0 } }>
      <div className="sm-panel-toggle" id="sm-colorize-elements-button" style={ { borderTopWidth: 0 } } onClick={ () => {
        wp.customize.section( targetSectionID, ( targetSection ) => {
          const backArray = getBackArray();
          setBackArray( [] );
          targetSection.focus();
          setBackArray( backArray );
          addToBackArray( 'sm_color_usage_section' );
        } );
      } }>
        <div className="sm-panel-toggle__icon" dangerouslySetInnerHTML={{
          __html: `
                <svg viewBox="${ colorizeElementsIcon.viewBox }">
                  <use xlink:href="#${ colorizeElementsIcon.id }" />
                </svg>`
        } } />
        <div className="sm-panel-toggle__label">
          Colorize elements one by one
        </div>
      </div>
    </div>
  )
}

const initializeColorizeElementsButton = () => {
  const target = document.getElementById( 'customize-control-sm_coloration_level_control' );
  const button = document.createElement( 'li' );

  button.setAttribute( 'class', 'customize-control' );
  button.setAttribute( 'style', 'padding: 0' );

  target.insertAdjacentElement( 'afterend', button );

  ReactDOM.render( <ColorizeElementsButton />, button );
}

const applyMasterSettingsValues = () => {
  masterSettingIDs.forEach( masterSettingID => {
    wp.customize( masterSettingID, setting => {
      setting.callbacks.fireWith( setting, [ setting._value, '' ] );
    } );
  } );
}

const reloadConnectedFields = () => {
  const settings = globalService.getSettings();
  const settingIDs = Object.keys( settings );
  const alteredSettings = applyColorsConnectedFieldsAlterations( settings );

  globalService.unbindConnectedFields( settingIDs );
  globalService.setSettings( alteredSettings );
  globalService.bindConnectedFields( settingIDs );
}

const applyColorationValueToFields = () => {

  wp.customize( 'sm_coloration_level', colorationLevelSetting => {
    const colorationLevel = colorationLevelSetting();
    const defaultColorationLevel = globalService.getSettingConfig( 'sm_coloration_level' ).default;
    const isDefaultColoration = colorationLevel === defaultColorationLevel;

    darkToColorSliderControls.forEach( sliderSettingID => {
      wp.customize( sliderSettingID, sliderSetting => {

        const defaultValue = globalService.getSettingConfig( sliderSettingID ).default;
        const value = isDefaultColoration ? defaultValue : parseFloat( colorationLevel );

        sliderSetting.set( value );
      } );
    } );

  } );

}

const applyColorationLevel = ( tempSettings ) => {
  const switchSliderID = 'sm_dark_color_switch_slider';
  const selectSliderID = 'sm_dark_color_select_slider';

  wp.customize( switchSliderID, switchSetting => {
    const switchRatio = switchSetting() / 100;
    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio );
  } );

  wp.customize( selectSliderID, selectSetting => {
    const selectRatio = selectSetting() / 100;
    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio );
  } );

  return tempSettings;
}

const applyColorsConnectedFieldsAlterations = ( tempSettings ) => {
  tempSettings = applyColorationLevel( tempSettings );

  return tempSettings
}

