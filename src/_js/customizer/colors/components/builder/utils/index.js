export { getPalettesFromColors } from './get-palettes-from-colors';
export { getCSSFromPalettes } from './get-css-from-palettes';

export const getColorsFromInputValue = ( value ) => {
  let colors;

  try {
    colors = JSON.parse( value );
  } catch( e ) {
    colors = [];
  }

  return colors;
}

export const getValueFromColors = ( colors ) => {
  return JSON.stringify( colors );
}






