import $ from 'jquery';
import _ from "lodash";

import * as filters from './filters';
export * from './create-current-palette-controls';
export * from './update-filter-previews';
export * from './apply-connected-fields-alterations';
export * from './update-color-pickers';
export * from './confirm-changes';

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
  return $( '[name*="sm_palette_filter"]:checked' ).val();
}

export const filterColor = ( hex, palette, label ) => {
  const filter = filters[label];

  if ( typeof filter === 'function' ) {
    return filter( hex, palette );
  }

  return hex;
}

export const filterPalette = ( palette, label ) => {
  return palette.map( hex => filterColor( hex, palette, label ) );
}

export const getFilteredColor = ( color ) => {
  const currentPalette = getCurrentPaletteColors();
  const activeFilter = getActiveFilter();

  return filterColor( color, currentPalette, activeFilter );
}

export const getFilteredColorByID = ( settingID ) => {
  const setting = wp.customize( settingID );
  const color = setting();

  return getFilteredColor( color );
}
