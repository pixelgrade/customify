import _ from 'lodash';
import $ from 'jquery';

import {
  filterColor,
  getCurrentPaletteColors,
  getFilteredColor,
  moveConnectedFields,
  swapConnectedFields,
  updateColorPickersAltered,
  updateColorPickersHidden,
  updateColorPickersSwatches,
} from './utils';

import {
  applyConnectedFieldsAlterations
} from "./utils";

import {
  createCurrentPaletteControls
} from './palette-controls';

import { globalService } from './global-service';

/** @namespace customify */
window.customify = window.customify || parent.customify || {};

const newMasterIDs = [
  'sm_accent_color_switch_master',
  'sm_accent_color_select_master',
  'sm_text_color_switch_master',
  'sm_text_color_select_master',
];

const switchColorSelector = '#_customize-input-sm_dark_color_switch_slider_control';
const selectColorSelector = '#_customize-input-sm_dark_color_select_slider_control';
const primaryColorSelector = '#_customize-input-sm_dark_color_primary_slider_control';
const secondaryColorSelector = '#_customize-input-sm_dark_color_secondary_slider_control';
const tertiaryColorSelector = '#_customize-input-sm_dark_color_tertiary_slider_control';

const colorSlidersSelector = [
  switchColorSelector,
  selectColorSelector,
  primaryColorSelector,
  secondaryColorSelector,
  tertiaryColorSelector
].join( ', ' );

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
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each(settingIDs, function (settingID) {
      const setting = wp.customize(settingID)

      if (typeof setting !== 'undefined') {
        let parentSettingData = globalService.getSetting( settingID )

        const finalValue = _.includes(newMasterIDs,settingID) ? setting() : getFilteredColor( settingID )

        _.each( parentSettingData.connected_fields, function( connectedFieldData ) {
          if ( _.isUndefined( connectedFieldData ) || _.isUndefined( connectedFieldData.setting_id ) || !_.isString( connectedFieldData.setting_id ) ) {
            return
          }
          const connectedSetting = wp.customize( connectedFieldData.setting_id )
          if ( _.isUndefined( connectedSetting ) ) {
            return
          }

          connectedSetting.set( finalValue )
        })

        // Also set the final setting value, for safe keeping.
        const finalSettingID = `${ settingID }_final`;
        const finalSetting = wp.customize( finalSettingID );
        if ( !_.isUndefined( finalSetting ) ) {
          if ( !_.isUndefined( parentSettingData.connected_fields ) && !_.isEmpty( parentSettingData.connected_fields ) ) {
            finalSetting.set( finalValue )
          } else {
            finalSetting.set( '' )
          }
        }
      }
    })
  }

  const getMasterFieldCallback = function( parentSettingData, parentSettingID ) {
    return function( newValue, oldValue ) {
      const finalValue = _.includes( newMasterIDs, parentSettingID ) ? newValue : getFilteredColor( parentSettingID )

      _.each( parentSettingData.connected_fields, function( connectedFieldData ) {
        if ( _.isUndefined( connectedFieldData ) || _.isUndefined( connectedFieldData.setting_id ) || !_.isString( connectedFieldData.setting_id ) ) {
          return
        }
        const setting = wp.customize( connectedFieldData.setting_id )
        if ( _.isUndefined( setting ) ) {
          return
        }

        setting.set( finalValue )
      } )
      updateFilterPreviews()

      // Also set the final setting value, for safe keeping.
      const finalSettingID = parentSettingID + '_final'
      const finalSetting = wp.customize( finalSettingID )
      if ( !_.isUndefined( finalSetting ) ) {
        if ( !_.isUndefined( parentSettingData.connected_fields ) && !_.isEmpty( parentSettingData.connected_fields ) ) {
          finalSetting.set( finalValue )
        } else {
          finalSetting.set( '' )
        }
      }
    }
  }

  const bindConnectedFields = function() {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each( settingIDs, function( parentSettingID ) {
      if ( typeof globalService.getSetting( parentSettingID ) !== 'undefined' ) {
        const parentSettingData = globalService.getSetting( parentSettingID );
        const parentSetting = wp.customize( parentSettingID );

        if ( !_.isUndefined( parentSettingData.connected_fields ) ) {
          globalService.setCallback( parentSettingID, getMasterFieldCallback( parentSettingData, parentSettingID ) );

          parentSetting.bind( globalService.setCallback( parentSettingID ) );

          _.each( parentSettingData.connected_fields, function( connectedFieldData ) {
            const connectedSettingID = connectedFieldData.setting_id;
            const connectedSetting = wp.customize( connectedSettingID );

            if ( typeof connectedSetting !== 'undefined' ) {
              globalService.setCallback( connectedSettingID, updateColorPickersAltered );
              connectedSetting.bind( globalService.getCallback( connectedSettingID ) );
            }
          } )
        }
      }
    } )
  }

  const unbindConnectedFields = function() {
    _.each( globalService.getCallbacks(), function( callback, settingID ) {
      const setting = wp.customize( settingID );
      setting.unbind( callback );
    } );
    globalService.deleteCallbacks();
  }

  const onPaletteChange = function () {
    $( this ).trigger( 'customify:preset-change' );
    reinitializeConnectedFields();
  }

  const refreshCurrentPaletteControl = () => {
    updateColorPickersAltered();
    updateColorPickersHidden();
    updateColorPickersSwatches();
  }

  const reloadConnectedFields = () => {
    applyConnectedFieldsAlterations( globalService.getSettings() ).then( settings => {
      console.log(
        settings['sm_text_color_switch_master']['connected_fields'],
        settings['sm_accent_color_switch_master']['connected_fields'],
      );
      const settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

      _.each( settingIDs, function( masterSettingId ) {
        globalService.setSetting( masterSettingId, settings[masterSettingId] );
      } );
    } );
  }

  const applyColorationValueToFields = () => {
    const $selectedColoration = $( '[name*="sm_coloration_level"]:checked' );
    const coloration = $selectedColoration.val();

    if ( typeof $selectedColoration.data( 'default' ) !== 'undefined' ) {

      const settingIDs = [
        'sm_dark_color_switch_slider',
        'sm_dark_color_select_slider',
        'sm_dark_color_primary_slider',
        'sm_dark_color_secondary_slider',
        'sm_dark_color_tertiary_slider',
      ];

      _.each( settingIDs, function( settingID ) {
        const sliderSetting = customify.config.settings[settingID];
        wp.customize( settingID ).set( sliderSetting.default );
      } );
    } else {
      $( colorSlidersSelector ).val( parseFloat( coloration ) ).change();
    }
  }

  const reinitializeConnectedFields = _.debounce( () => {
    reloadConnectedFields();
    unbindConnectedFields();
    bindConnectedFields();
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
    const $paletteControl = $( '.c-color-palette__control' );

    $paletteControl.removeClass( 'active' )

    $( document ).on( 'click', '.js-color-palette input', function( e ) {
      if ( ! confirmChanges( onPaletteChange.bind( this ) ) ) {
        e.preventDefault()
      }
    } );

    $( '[for*="sm_palette_filter"], [for*="sm_coloration_level"], [for*="sm_color_diversity"], [for*="sm_shuffle_colors"], [for*="sm_dark_mode"]' ).on( 'click', function( e ) {
      if ( ! confirmChanges() ) {
        e.preventDefault()
      }
    } );

    $( colorSlidersSelector ).on( 'change', reinitializeConnectedFields );

    $( '[name*="sm_coloration_level"]' ).on( 'change', applyColorationValueToFields )
    $( '[name*="sm_color_diversity"]' ).on( 'change', reinitializeConnectedFields )
    $( '[name*="sm_shuffle_colors"]' ).on( 'change', reinitializeConnectedFields )
    $( '[name*="sm_dark_mode"]' ).on( 'change', reinitializeConnectedFields )
    $( '[name*="sm_palette_filter"]' ).on( 'change', reinitializeConnectedFields );
  }

  const updateFilterPreviews = _.debounce( () => {
    const currentPalette = getCurrentPaletteColors();

    $( '.sm-palette-filter' ).each( function() {
      const $filters = $( this ).find( 'input' );

      $filters.each( function( i, obj ) {
        const $input = $( obj );
        const $label = $input.next( 'label' );
        const label = $input.val();
        const $colors = $label.find( '.sm-color-palette__color' );

        $colors.each( function( j, color ) {
          const $color = $( color );
          const settingID = $color.data( 'setting' );
          const setting = wp.customize( settingID );
          const originalColor = setting();

          $color.css( 'color', filterColor( originalColor, currentPalette, label ) );
        } );
      } );
    } );
  }, 30 )

  wp.customize.bind('ready', function () {
    // We need to do this here to be sure the data is available.
    globalService.loadSettings();

    createCurrentPaletteControls();

    reloadConnectedFields();
    unbindConnectedFields();
    bindConnectedFields();
    refreshCurrentPaletteControl();

    updateFilterPreviews();

    bindEvents();
  })

  return {}
}() )
