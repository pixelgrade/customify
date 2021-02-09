import _ from 'lodash';
import $ from 'jquery';

import { globalService } from './global-service';

import {
  applyConnectedFieldsAlterations,
  updateConnectedFieldsValue,

  bindConnectedFields,
  unbindConnectedFields,
} from "./utils";

import {
  initializePaletteBuilder,
  getCSSFromPalettes,
} from './components/builder';

/** @namespace customify */
window.customify = window.customify || parent.customify || {};

const masterSettingIDs = [
  'sm_accent_color_switch_master',
  'sm_accent_color_select_master',
  'sm_text_color_switch_master',
  'sm_text_color_select_master',
];

const darkToColorSliderControls = [
  'sm_dark_color_switch_slider',
  'sm_dark_color_select_slider',
];

/**
 * Expose the API publicly on window.customify.api
 *
 * @namespace customify.api
 */
if ( typeof customify.api === 'undefined' ) {
  customify.api = {};
}

customify.api = Object.assign( {}, customify.api, ( function() {

  const reloadConnectedFields = _.debounce( () => {
    globalService.loadSettings();

    const settings = applyConnectedFieldsAlterations( globalService.getSettings() );

    globalService.setSettings( settings );

    unbindConnectedFields( masterSettingIDs );
    bindConnectedFields( masterSettingIDs );

    masterSettingIDs.forEach( settingID => {
      globalService.setSetting( settingID, settings[ settingID ] );
      const setting = wp.customize( settingID );

      if ( typeof setting !== "undefined" ) {
        updateConnectedFieldsValue( settingID, setting() );
      }
    } );
  }, 30 );

  const applyColorationValueToFields = () => {
    const $selectedColoration = $( '[name*="sm_coloration_level"]:checked' );
    const coloration = $selectedColoration.val();
    const isDefaultColoration = typeof $selectedColoration.data( 'default' ) !== 'undefined';

    darkToColorSliderControls.forEach( settingID => {
      const setting = wp.customize( settingID );
      const defaultValue = customify.config.settings[ settingID ].default;
      const value = isDefaultColoration ? defaultValue : parseFloat( coloration );

      setting.set( value );
    } );
  }

  const bindEvents = () => {
    wp.customize( 'sm_coloration_level', setting => {
      setting.bind( applyColorationValueToFields );
    } );
  }

  wp.customize.bind( 'ready', function() {
    // we need to do this here to be sure the data is available.
    globalService.loadSettings();

    initializePaletteBuilder( 'sm_advanced_palette_source', 'sm_advanced_palette_output' );

    reloadConnectedFields();

    bindEvents();
  } )

  return {
    getCSSFromPalettes,
  }

})() );
