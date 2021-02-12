import { initializePaletteBuilder } from './color-palette-builder';
import { moveConnectedFields } from './utils';
import * as globalService from "../global-service";
import _ from "lodash";

wp.customize.bind( 'ready', () => {
  initializeColors();
  reloadConnectedFields();
} );

const initializeColors = () => {

  initializePaletteBuilder( 'sm_advanced_palette_source', 'sm_advanced_palette_output' );

  wp.customize( 'sm_coloration_level', setting => {
    setting.bind( applyColorationValueToFields );
  } );

}

const reloadConnectedFields = _.debounce( () => {
  const settings = globalService.getSettings();
  const settingIDs = Object.keys( settings );

  globalService.unbindConnectedFields( settingIDs );
  globalService.setSettings( applyColorsConnectedFieldsAlterations( settings ) );
  globalService.bindConnectedFields( settingIDs );

}, 30 );

const applyColorationValueToFields = () => {

  wp.customize( 'sm_coloration_level', colorationLevelSetting => {
    const colorationLevel = colorationLevelSetting();
    const defaultColorationLevel = globalService.getSetting( 'sm_coloration_level' ).default
    const isDefaultColoration = colorationLevel === defaultColorationLevel;

    const darkToColorSliderControls = [
      'sm_dark_color_switch_slider',
      'sm_dark_color_select_slider',
    ];

    darkToColorSliderControls.forEach( sliderSettingID => {
      wp.customize( sliderSettingID, sliderSetting => {

        const defaultValue = globalService.getSetting( sliderSettingID ).default;
        const value = isDefaultColoration ? defaultValue : parseFloat( colorationLevel );

        sliderSetting.set( value );
      } );
    } );

  } );

}

const applyColorationLevel = ( tempSettings ) => {
  const switchSliderID = 'sm_dark_color_switch_slider';
  const selectSliderID = 'sm_dark_color_select_slider';

  wp.customize( 'sm_coloration_level', colorationLevelSetting => {
    const colorationLevel = colorationLevelSetting();
    const defaultColorationLevel = globalService.getSetting( 'sm_coloration_level' ).default
    const isDefaultColoration = colorationLevel === defaultColorationLevel;

    if ( isDefaultColoration ) {
      return;
    }

    wp.customize( switchSliderID, switchSetting => {
      const switchRatio = switchSetting() / 100;
      tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio );
    } );

    wp.customize( selectSliderID, selectSetting => {
      const selectRatio = selectSetting() / 100;
      tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio );
    } );
  } );

  return tempSettings;
}

const applyColorsConnectedFieldsAlterations = ( tempSettings ) => {
  tempSettings = applyColorationLevel( tempSettings );

  return tempSettings
}

