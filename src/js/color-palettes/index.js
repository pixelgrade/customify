import _ from 'lodash';
import $ from 'jquery';

import { globalService } from './global-service';

import {
  getFilteredColor,
  applyConnectedFieldsAlterations,
  createCurrentPaletteControls,
  updateConnectedFieldsValue,

  updatePalettePreview,
  updateFilterPreviews,
  updateColorPickersAltered,
  updateColorPickersHidden,
  updateColorPickersSwatches,
} from "./utils";

import {
  initializePaletteBuilder,
  getCSSFromPalettes,
} from './components/builder';

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
 * Expose the API publicly on window.customify.api
 *
 * @namespace customify.api
 */
if ( typeof customify.api === 'undefined' ) {
  customify.api = {};
}

customify.api = Object.assign( {}, customify.api, ( function() {

  const bindConnectedFields = function() {
    const settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each( settingIDs, ( settingID ) => {
      const parentSettingData = globalService.getSetting( settingID );

      if ( typeof parentSettingData !== 'undefined' ) {
        _.each( parentSettingData.connected_fields, ( connectedFieldData ) => {
          const connectedSettingID = connectedFieldData.setting_id;
          const connectedSetting = wp.customize( connectedSettingID );

          if ( typeof connectedSetting !== 'undefined' ) {
            globalService.setCallback( connectedSettingID, updateColorPickersAltered );
            connectedSetting.bind( globalService.getCallback( connectedSettingID ) );
          }
        } )
      }
    } );
  }

  const unbindConnectedFields = function() {
    _.each( globalService.getCallbacks(), function( callback, settingID ) {
      const setting = wp.customize( settingID );
      setting.unbind( callback );
    } );
    globalService.deleteCallbacks();
  }

  const reloadConnectedFields = _.debounce( () => {
    globalService.loadSettings();

    const settings = applyConnectedFieldsAlterations( globalService.getSettings() );

    globalService.setSettings( settings );

    unbindConnectedFields();
    bindConnectedFields();

    customify.colorPalettes.masterSettingIds.forEach( settingID => {
      globalService.setSetting( settingID, settings[ settingID ] );
      const setting = wp.customize( settingID );
      const finalSetting = wp.customize( `${ settingID }_final` );

      if ( typeof setting !== "undefined" && typeof finalSetting !== "undefined" ) {
        updateConnectedFieldsValue( settingID, finalSetting() );
      }
    } );

    newMasterIDs.forEach( settingID => {
      globalService.setSetting( settingID, settings[ settingID ] );
      const setting = wp.customize( settingID );

      if ( typeof setting !== "undefined" ) {
        updateConnectedFieldsValue( settingID, setting() );
      }
    } );

    updateColorPickersHidden();
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

  const bindFilteredColors = () => {

    const updatePreview = _.debounce( ( newValue ) => {
      updatePalettePreview();
      updateFilterPreviews();
      updateColorPickersSwatches();
    }, 20 );

    customify.colorPalettes.masterSettingIds.forEach( settingID => {
      const setting = wp.customize( settingID );
      const finalSetting = wp.customize( `${ settingID }_final` );

      if ( typeof setting !== "undefined" && typeof finalSetting !== "undefined" ) {
        setting.bind( ( newValue, oldValue ) => {
          finalSetting.set( getFilteredColor( newValue ) );
        } );

        finalSetting.bind( ( newValue ) => {
          updateConnectedFieldsValue( settingID, newValue );
          updatePreview();
        } );
      }
    } );

    newMasterIDs.forEach( settingID => {
      const setting = wp.customize( settingID );
      if ( typeof setting !== "undefined" ) {
        setting.bind( ( newValue, oldValue ) => {
          updateConnectedFieldsValue( settingID, newValue );
        } );
      }
    } );

    const filterSetting = wp.customize( 'sm_palette_filter' );

    if ( typeof filterSetting !== "undefined" ) {
      filterSetting.bind( onFilterChange );
    }
  }

  const onFilterChange = ( newFilter, oldFilter ) => {

    customify.colorPalettes.masterSettingIds.forEach( settingID => {
      const setting = wp.customize( settingID );
      const finalSetting = wp.customize( `${ settingID }_final` );

      if ( typeof setting !== "undefined" && typeof finalSetting !== "undefined" ) {
        const color = setting();
        finalSetting.set( getFilteredColor( color, newFilter ) );
      }
    } );
  };

  const bindConnectedFieldsAlterations = () => {
    const alterationControls = [ 'sm_color_diversity', 'sm_shuffle_colors', 'sm_dark_mode' ];

    alterationControls.concat( darkToColorSliderControls ).forEach( settingID => {
      const setting = wp.customize( settingID );
      if ( typeof setting !== "undefined" ) {
        setting.bind( reloadConnectedFields );
      }
    } );
  }

  const bindEvents = () => {
    bindConfirmChanges();

    wp.customize( 'sm_coloration_level' ).bind( applyColorationValueToFields );

    bindFilteredColors();
    bindConnectedFieldsAlterations();
  }

  function onPaletteChange() {
    const $target = $( this );
    const options = $target.data( 'options' );
    const colorSettingIds = [ 'sm_color_primary', 'sm_color_secondary', 'sm_color_tertiary' ];
    const sources = Object.keys( options ).filter( settingId => colorSettingIds.includes( settingId ) ).map( ( settingId, index ) => {
      return {
        label: `Color ${ 'ABC'[index] }`,
        value: options[ settingId ]
      }
    } );

    const setting = wp.customize( 'sm_advanced_palette_source' );

    if ( typeof setting !== 'undefined' ) {
      setting.set( JSON.stringify( sources ) );
    }
  }

  const bindConfirmChanges = () => {
    // confirm changes before changing the color palette
    $( document ).on( 'click', '.js-color-palette input', function( e ) {
      if ( ! confirmChanges( onPaletteChange.bind( this ) ) ) {
        e.preventDefault();
      }
    } );

    // confirm changes before changing the color palette
    const controls = [ 'sm_palette_filter', 'sm_coloration_level', 'sm_color_diversity', 'sm_shuffle_colors', 'sm_dark_mode' ];
    const selector = controls.map( name => `[for*="${ name }"]` ).join( ', ' );

    $( document ).on( 'click', selector, function( e ) {
      if ( ! confirmChanges() ) {
        e.preventDefault();
      }
    } );
  }

  wp.customize.bind( 'ready', function() {
    // We need to do this here to be sure the data is available.
    globalService.loadSettings();

    initializePaletteBuilder( 'sm_advanced_palette_source', 'sm_advanced_palette_output' );
    createCurrentPaletteControls();
    reloadConnectedFields();

    updateFilterPreviews();
    updateColorPickersAltered();
    updateColorPickersHidden();

    bindEvents();
  } )

  return {
    getCSSFromPalettes,
  }

})() );
