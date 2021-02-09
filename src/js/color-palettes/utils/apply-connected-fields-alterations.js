import _ from "lodash";
import $ from "jquery";

import {
  moveConnectedFields,
  swapConnectedFields
} from "./connected-fields";

const isDefaultColorationSet = () => {
  return false;
}

const applyColorationLevel = ( tempSettings ) => {
  const switchColorSelector = '#_customize-input-sm_dark_color_switch_slider_control';
  const selectColorSelector = '#_customize-input-sm_dark_color_select_slider_control';

  if ( ! isDefaultColorationSet() ) {
    const switchRatio = $( switchColorSelector ).val() / 100;
    const selectRatio = $( selectColorSelector ).val() / 100;

    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio );
    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio );
  }

  return tempSettings;
}

export const applyConnectedFieldsAlterations = ( tempSettings ) => {
  tempSettings = applyColorationLevel( tempSettings );

  return tempSettings
}
