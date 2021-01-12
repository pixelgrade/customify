import $ from 'jquery';
import _ from "lodash";

import * as filters from './filters';
export * from './connected-fields';
export * from './update-color-pickers';

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

export const getFilteredColor = (settingID) => {
  const currentPalette = getCurrentPaletteColors();
  const activeFilter = getActiveFilter();
  const setting = wp.customize( settingID );
  const initialColor = setting();

  return filterColor( initialColor, currentPalette, activeFilter );
}
