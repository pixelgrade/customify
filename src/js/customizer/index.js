import $ from 'jquery';

import './colors';
import './fonts';
import './font-palettes';

import * as globalService from "./global-service";

import { handleColorSelectFields } from './fields/color-select';
import { handleRangeFields } from './fields/range';

import { handleFoldingFields } from './folding-fields';
import { scalePreview } from './scale-preview';
import { createResetButtons } from './create-reset-buttons';

wp.customize.bind( 'ready', () => {
  globalService.loadSettings();

  const settings = globalService.getSettings();
  const settingIDs = Object.keys( settings );

  settingIDs.forEach( settingID => {
    wp.customize( settingID, setting => {
      setting.bind( newValue => {
        const settingConfig = globalService.getSetting( settingID );
        const connectedFields = settingConfig.connected_fields || {};

        Object.keys( connectedFields ).map( key => connectedFields[key].setting_id ).forEach( connectedSettingID => {
          wp.customize( connectedSettingID, connectedSetting => {
            connectedSetting.set( newValue );
          } );
        } );
      } );
    } );
  } );

  createResetButtons();
  handleRangeFields();
  handleColorSelectFields();

  // @todo check reason for this timeout
  setTimeout( function () {
    handleFoldingFields();
  }, 1000 );

  // Initialize simple select2 fields.
  $( '.customify_select2' ).select2();

  scalePreview();

} );

// expose API on sm.customizer global object
export { getFontDetails } from './fonts/utils';





