import $ from 'jquery';
import _ from "lodash";

import * as filters from './filters';
export * from './create-current-palette-controls';
export * from './update-filter-previews';
export * from './apply-connected-fields-alterations';
export * from './update-color-pickers';
export * from './confirm-changes';
export * from './connected-fields';

export const getCurrentPaletteColors = () => {
  const colors = []
  _.each( customify.colorPalettes.masterSettingIds, function( settingID ) {
    const setting = wp.customize( settingID );

    if ( typeof setting !== 'undefined' ) {
      const color = setting();
      colors.push( color );
    }
  } );
  return colors
}

export const getActiveFilter = () => {
  const filterSetting = wp.customize( 'sm_palette_filter' );
  return filterSetting();
}

export const getFilteredColor = ( hex, filterLabel, colors ) => {
  const label = typeof filterLabel !== "undefined" ? filterLabel : getActiveFilter();
  const filter = filters[label];
  const palette = typeof colors !== "undefined" ? colors : getCurrentPaletteColors();

  if ( typeof filter === 'function' ) {
    return filter( hex, palette );
  }

  return hex;
}

export const getFilteredColorByID = ( settingID, filterLabel, colors ) => {
  const setting = wp.customize( settingID );
  const color = setting();

  return getFilteredColor( color, filterLabel, colors );
}
