import { initializePaletteBuilder } from './color-palette-builder';
import { moveConnectedFields } from './utils';
import './color-palettes-preview';
import * as globalService from "../global-service";
import { debounce } from '../../utils';

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

  reloadConnectedFields();
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
