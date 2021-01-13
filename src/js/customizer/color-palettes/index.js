import _ from 'lodash';
import $ from 'jquery';

import { globalService } from './global-service';

import {
  getFilteredColorByID,
  applyConnectedFieldsAlterations,
  createCurrentPaletteControls,
  updateFilterPreviews,
  updateColorPickersAltered,
  updateColorPickersHidden,
  updateColorPickersSwatches,
} from "./utils";

import { updateConnectedFieldsValue } from "./utils/connected-fields/update-connected-fields-value";

/** @namespace customify */
window.customify = window.customify || parent.customify || {};

const newMasterIDs = [
  'sm_accent_color_switch_master',
  'sm_accent_color_select_master',
  'sm_text_color_switch_master',
  'sm_text_color_select_master',
];

const darkToColorSliderControls = [
  'sm_dark_color_switch_slider',
  'sm_dark_color_select_slider',
  'sm_dark_color_primary_slider',
  'sm_dark_color_secondary_slider',
  'sm_dark_color_tertiary_slider',
];

/**
 * Expose the API publicly on window.customify.colorPalettes
 *
 * @namespace customify.colorPalettes
 */
if ( typeof customify.colorPalettes === 'undefined' ) {
  customify.colorPalettes = {}
}

_.extend( customify.colorPalettes, function () {

  const resetSettings = () => {
    updateConnectedFieldsOld();
    updateConnectedFields( newMasterIDs );
  }

  const updateConnectedFieldsOld = () => {
    _.each( customify.colorPalettes.masterSettingIds, function( settingID ) {
      const setting = wp.customize( settingID );

      if ( typeof setting !== 'undefined' ) {
        const finalValue = getFilteredColorByID( settingID );

        updateConnectedFieldsValue( settingID, finalValue );
        updateFinalSettingValue( settingID, finalValue );
      }
    } );
  }

  const updateFinalSettingValue = ( settingID, value ) => {
    // Also set the final setting value, for safe keeping.
    const finalSettingID = `${ settingID }_final`;
    const finalSetting = wp.customize( finalSettingID );
    if ( !_.isUndefined( finalSetting ) ) {
      finalSetting.set( value );
    }
  }

  const updateConnectedFields = ( settingIDs, filter ) => {
    _.each( settingIDs, function( settingID ) {
      const setting = wp.customize( settingID );

      if ( typeof setting !== 'undefined' ) {
        let settingValue = setting();
        let finalValue = typeof filter !== "function" ? settingValue : filter( settingValue );
        updateConnectedFieldsValue( settingID, finalValue );
      }
    } );
  }

  const getMasterFieldCallback = function( parentSettingData, parentSettingID ) {
    return function( newValue, oldValue ) {
      const finalValue = _.includes( newMasterIDs, parentSettingID ) ? newValue : getFilteredColorByID( parentSettingID )

      updateConnectedFieldsValue( parentSettingID, finalValue );
      updateFinalSettingValue( parentSettingID, finalValue );
      updateFilterPreviews();
    }
  }

  const bindConnectedFields = function() {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each( settingIDs, bindParentCallbacks );
  }

  const bindParentCallbacks = ( parentSettingID ) => {

    if ( typeof globalService.getSetting( parentSettingID ) !== 'undefined' ) {

      const parentSettingData = globalService.getSetting( parentSettingID );
      const parentSetting = wp.customize( parentSettingID );

      if ( !_.isUndefined( parentSettingData.connected_fields ) ) {
        globalService.setCallback( parentSettingID, getMasterFieldCallback( parentSettingData, parentSettingID ) );
        parentSetting.bind( globalService.setCallback( parentSettingID ) );

        _.each( parentSettingData.connected_fields, bindConnectedFieldsCallbacks )
      }
    }
  }

  const bindConnectedFieldsCallbacks = ( connectedFieldData ) => {
    const connectedSettingID = connectedFieldData.setting_id;
    const connectedSetting = wp.customize( connectedSettingID );

    if ( typeof connectedSetting !== 'undefined' ) {
      globalService.setCallback( connectedSettingID, updateColorPickersAltered );
      connectedSetting.bind( globalService.getCallback( connectedSettingID ) );
    }
  }

  const unbindConnectedFields = function() {
    _.each( globalService.getCallbacks(), function( callback, settingID ) {
      const setting = wp.customize( settingID );
      setting.unbind( callback );
    } );
    globalService.deleteCallbacks();
  }

  const refreshCurrentPaletteControl = () => {
    updateColorPickersAltered();
    updateColorPickersHidden();
    updateColorPickersSwatches();
  }

  const reloadConnectedFields = () => {
    const settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );
    const settings = applyConnectedFieldsAlterations( globalService.getSettings() )

    _.each( settingIDs, function( masterSettingId ) {
      globalService.setSetting( masterSettingId, settings[masterSettingId] );
    } );

    unbindConnectedFields();
    bindConnectedFields();
  }

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

  const reinitializeConnectedFields = _.debounce( () => {
    reloadConnectedFields();
    refreshCurrentPaletteControl();
    resetSettings();
  }, 30 );

  const confirmChanges = ( callback ) => {
    const altered = $( '.c-color-palette .color.altered' ).length
    let confirmed = true

    if ( altered ) {
      confirmed = confirm( 'One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action.' )
    }

    if ( ! altered || confirmed ) {
      if ( typeof callback === 'function' ) {
        callback()
      }
      return true
    }

    return false
  }

  const bindEvents = () => {
    bindConfirmChanges();

    wp.customize( 'sm_coloration_level' ).bind( applyColorationValueToFields );

    const alterationControls = [ 'sm_color_diversity', 'sm_shuffle_colors', 'sm_dark_mode', 'sm_palette_filter' ];

    alterationControls.concat( darkToColorSliderControls ).forEach( settingID => {
      const setting = wp.customize( settingID );
      if ( typeof setting !== "undefined" ) {
        setting.bind( reinitializeConnectedFields );
      }
    } );
  }

  const onPaletteChange = function () {
    console.log( this, $( this ) );
    $( this ).trigger( 'customify:preset-change' );
    reinitializeConnectedFields();
  }

  const bindConfirmChanges = () => {
    // confirm changes before changing the color palette
    $( document ).on( 'click', '.js-color-palette input', function( e ) {
      if ( ! confirmChanges( onPaletteChange.bind( this ) ) ) {
        e.preventDefault()
      }
    } );

    // confirm changes before changing the color palette
    const controls = [ 'sm_palette_filter', 'sm_coloration_level', 'sm_color_diversity', 'sm_shuffle_colors', 'sm_dark_mode' ];
    const selector = controls.map( name => `[for*="${ name }"]` ).join( ', ' );

    $( document ).on( 'click', selector, function( e ) {
      if ( ! confirmChanges() ) {
        e.preventDefault()
      }
    } );
  }

  wp.customize.bind( 'ready', function() {
    // We need to do this here to be sure the data is available.
    globalService.loadSettings();

    createCurrentPaletteControls();
    reloadConnectedFields();

    refreshCurrentPaletteControl();
    updateFilterPreviews();

    bindEvents();
  } )

  return {}
}() )
