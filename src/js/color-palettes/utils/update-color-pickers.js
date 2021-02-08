import _ from "lodash";
import $ from "jquery";

import {
  getFilteredColor,
  getFilteredColorByID,
  getCurrentPaletteColors, getActiveFilter
} from "./index";

import {
  globalService
} from "../global-service";

// this function goes through all the connected fields and adds swatches to the default color picker for all the colors in the current color palette
export const updateColorPickersSwatches = _.debounce(() => {

  let $targets = $();

  // loop through the master settings
  _.each( customify.colorPalettes.masterSettingIds, function( parentSettingID ) {
    if ( typeof globalService.getSetting( parentSettingID ) !== 'undefined' ) {
      const parentSettingData = globalService.getSetting( parentSettingID );

      if ( !_.isUndefined( parentSettingData.connected_fields ) ) {

        // loop through all the connected fields and search the element on which the iris plugin has been initialized
        _.each( parentSettingData.connected_fields, function( connectedFieldData ) {

          // the connected_setting_id is different than the actual id attribute of the element we're searching for
          // so we have to do some regular expressions
          const connectedSettingID = connectedFieldData.setting_id;
          const matches = connectedSettingID.match( /\[(.*?)\]/ );

          if ( matches ) {
            const targetID = matches[1];
            const $target = $( '.customize-control-color' ).filter( '[id*="' + targetID + '"]' ).find( '.wp-color-picker' );
            $targets = $targets.add( $target );
          }
        } )
      }
    }
  } )

  // apply the current color palettes to all the elements found
  $targets.iris( {
    palettes: getCurrentPaletteColors()
  } );

}, 30);

export const updateColorPickersAltered = _.debounce( () => {
  const alteredSettings = [];
  let alteredSettingsSelector

  _.each( customify.colorPalettes.masterSettingIds, function( masterSettingId ) {
    let masterSetting = globalService.getSetting( masterSettingId );

    if ( ! masterSetting ) {
      return false;
    }

    let connectedFields = masterSetting['connected_fields'];
    let masterSettingValue = wp.customize( masterSettingId )();
    let connectedFieldsWereAltered = false;

    if ( !_.isUndefined( connectedFields ) && !Array.isArray( connectedFields ) ) {
      connectedFields = Object.keys( connectedFields ).map( function( key ) {
        return connectedFields[key]
      } )
    }

    if ( !_.isUndefined( connectedFields ) && connectedFields.length ) {
      _.each( connectedFields, function( connectedField ) {
        const connectedSettingId = connectedField.setting_id;
        const connectedSetting = wp.customize( connectedSettingId );

        if ( typeof connectedSetting !== 'undefined' ) {
          const connectedFieldValue = connectedSetting();
          const filteredColor = getFilteredColorByID( masterSettingId );

          if ( typeof connectedFieldValue === 'string' && connectedFieldValue.toLowerCase() !== filteredColor.toLowerCase() ) {
            connectedFieldsWereAltered = true
          }
        }
      } )

      if ( connectedFieldsWereAltered ) {
        alteredSettings.push( masterSettingId )
      }
    }
  } )

  alteredSettingsSelector = '.' + alteredSettings.join( ', .' );

  $( '.c-color-palette .color' ).removeClass( 'altered' )

  if ( alteredSettings.length ) {
    $( '.c-color-palette .color' ).filter( alteredSettingsSelector ).addClass( 'altered' )
  }

}, 30 );

export const updateColorPickersHidden = () => {
  const optionsToShow = []
  let optionsSelector

  _.each( customify.colorPalettes.masterSettingIds, function( masterSettingId ) {
    const masterSetting = globalService.getSetting( masterSettingId );

    if ( ! masterSetting ) {
      return false;
    }

    const connectedFields = masterSetting['connected_fields']

    if ( !_.isUndefined( connectedFields ) && !_.isEmpty( connectedFields ) ) {
      optionsToShow.push( masterSettingId )
    }
  } )

  if ( !_.isEmpty( optionsToShow ) ) {
    optionsSelector = '.' + optionsToShow.join( ', .' )
  } else {
    optionsSelector = '*'
  }

  const $target = $( '.sm-palette-filter .color, .sm-color-palette__color, .js-color-palette .palette__item' );

  $target.addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' );
}

export const updatePalettePreview = function( filtered = true ) {

  const getFilteredColorFromSettingID = ( settingID ) => {
    const activeFilter = getActiveFilter();
    const currentColors = getCurrentPaletteColors();
    return getFilteredColorByID( settingID, activeFilter, currentColors );
  }

  const getOriginalColorFromSettingID = ( settingID ) => {
    const setting = wp.customize( settingID );
    return setting();
  }

  const getColor = filtered ? getFilteredColorFromSettingID : getOriginalColorFromSettingID;

  customify.colorPalettes.masterSettingIds.forEach( function( settingID ) {
    $( '.c-color-palette' ).find( `.sm-color-palette__color.${ settingID }` ).css( 'color', getColor( settingID ) );
  } );

}
