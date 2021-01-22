import { hexToHpluv, hpluvToRgb } from 'hsluv';
import chroma from 'chroma-js';

import contrastArray from './contrast-array';

const attributes = {
  correctLightness: true,
  useSources: true,
}

export const getPalettesFromColors = ( colors => {
  return colors.map( mapColorToPalette( attributes ) )
               .map( mapInterpolateSource( attributes ) )
               .map( mapCorrectLightness( attributes ) )
               .map( mapUpdateProps )
               .map( mapUseSource( attributes ) )
               .map( mapAddSourceIndex )
               .map( mapAddColorCategories );

} );

export const getSourceIndex = ( palette ) => {
  return palette.colors.findIndex( color => color.value === palette.source )
}

export const mapAddSourceIndex = ( palette, index, palettes ) => {
  return {
    sourceIndex: getSourceIndex( palette ),
    ...palette
  };
}

export const getShiftedArray = ( array, positions ) => {
  const arrayClone = array.slice();
  const chunk = arrayClone.splice( 0, positions );
  arrayClone.push( ...chunk );
  return arrayClone;
}

export const mapShiftColors = ( palette ) => {
  palette.colors = getShiftedArray( palette.colors );

  return palette;
}

const isDarkColor = ( hex ) => {
  return chroma.contrast( hex, 'white' ) > Math.sqrt( 21 )
}

export const mapAddColorCategories = ( palette, paletteIndex, palettes ) => {
  const { colors, source } = palette;

  palette.colors = palette.colors.map( ( color, colorIndex, colors ) => {
    const hex = color.value;

    return {
      background: hex,
      dark: isDarkColor( hex ) ? '#FFFFFF' : getTextColor( source, 9 ),
      darker: isDarkColor( hex ) ? '#FFFFFF' : getTextColor( source, 10 ),
      accent: colors[ ( colorIndex + 5 ) % colors.length ].value,
    }
  } );

  return palette;
}

export const mapColorToPalette = ( ( attributes ) => {
  const { mode } = attributes;

  return ( colorObj ) => {
    const color = colorObj.value;
    const label = colorObj.label;
    const reference = chroma( color ).set( 'hsv.s', 1 ).set( 'hsv.v', 1 ).hex();

    const colors = contrastArray.map( contrast => {
      const luminance = contrastToLuminance( contrast );
      return chroma( color ).luminance( luminance, mode ).hex();
    } );

    return {
      label: label,
      source: color,
      reference: reference,
      colors: colors,
    };
  }
} );

export const mapInterpolateSource = ( attributes ) => {
  const { mode } = attributes;

  return ( palette ) => {
    const { source } = palette;
    const position = getBestPositionInPaletteByLuminance( source, palette.colors, attributes );

    if ( mode !== 'none' ) {

      const stops = [
        { value: '#FFFFFF', position: 0 },
        { value: source, position: position },
        { value: '#000000', position: 11 }
      ];

      palette.colors = getColorsFromStops( stops, attributes );
    }

    return palette;
  }
}

const getColorsFromStops = ( stops, attributes ) => {

  const { mode } = attributes;
  const colors = [ stops[0].value ];

  for ( let i = 0; i < stops.length - 1; i++ ) {
    const scale = chroma.scale( [ stops[i].value, stops[i + 1].value ] ).mode( mode );
    const scaleColors = scale.colors( stops[i + 1].position - stops[i].position + 1 );
    colors.push( ...scaleColors.slice( 1 ) );
  }

  return colors;
}

export const mapCorrectLightness = ( { correctLightness, mode } ) => {

  if ( ! correctLightness ) {
    return noop;
  }

  return ( palette ) => {
    palette.colors = palette.colors.map( ( color, index ) => {
      const luminance = contrastToLuminance( contrastArray[ index ] );
      return chroma( color ).luminance( luminance, mode !== 'none' ? mode : 'rgb' ).hex();
    } );
    return palette;
  }
}

const mapUpdateProps = ( palette ) => {
  palette.colors = palette.colors.map( ( color, index ) => {
    return Object.assign( {}, {
      value: color,
    } )
  } );

  return palette;
}

export const mapUseSource = ( attributes ) => {

  if ( ! attributes.useSources ) {
    return noop;
  }

  return ( palette ) => {
    const { source } = palette;
    const position = getBestPositionInPaletteByLuminance( source, palette.colors.map( color => color.value ), attributes );

    palette.colors.splice( position, 1, {
      value: source,
      isSource: true
    } );

    return palette;
  }
}

export const getBestPositionInPaletteByLuminance = ( color, colors, attributes, byColorDistance ) => {
  let min = Number.MAX_SAFE_INTEGER;
  let pos = -1;

  for ( let i = 0; i < colors.length - 1; i++ ) {
    let distance;

    if ( !! byColorDistance ) {
      distance = chroma.distance( colors[i], color );
    } else {
      distance = Math.abs( chroma( colors[i] ).luminance() - chroma( color ).luminance() );
    }

    if ( distance < min ) {
      min = distance;
      pos = i;
    }
  }

  let firstDarkPos = Math.ceil( colors.length / 2 );

  // if we want to preserve contrast we should do this
  if ( attributes?.correctLightness ) {
    if ( chroma.contrast( color, 'white' ) > Math.sqrt( 21 ) ) {
      pos = Math.max( firstDarkPos, pos );
    } else {
      pos = Math.min( firstDarkPos - 1, pos );
    }
  }

  return pos;
}

const getTextColor = ( source, position, mode ) => {
  const luminance = contrastToLuminance( contrastArray[ position ] );
  const hpluv = hexToHpluv( source );

  const h = Math.min( Math.max( hpluv[0], 0), 360 );
  const p = Math.min( Math.max( hpluv[1], 0), 100 );
  const l = Math.min( Math.max( hpluv[2], 0), 100 );
  const rgb = hpluvToRgb( [h, p, l] ).map( x => Math.max(0, Math.min( x * 255, 255 ) ) )

  return chroma( rgb ).luminance( luminance, mode ).hex();
}

const mapAddTextColor = ( position, attributes ) => {
  const { mode } = attributes;

  return ( palette ) => {
    const { source } = palette;
    palette.colors.push( { value: getTextColor( source, position, mode ) } );
    return palette;
  }
}

const contrastToLuminance = ( contrast ) => {
  return 1.05 / contrast - 0.05;
}

export const getVariablesCSS = ( colors ) => {
  return colors.reduce( ( colorsAcc, color, colorIndex ) => {
    return `${ colorsAcc }
        --sm-color-${ colorIndex }: ${ color.background };
        `;
  }, '' );
}

export const getVariationVariablesCSS = ( colors ) => {
  return colors.reduce( ( colorsAcc, color, colorIndex ) => {
    return `${ colorsAcc }
        --sm-background-color-${ colorIndex }: ${ color.background };
        --sm-dark-color-${ colorIndex }: ${ color.dark };
        --sm-darker-color-${ colorIndex }: ${ color.darker };
        --sm-accent-color-${ colorIndex }: ${ color.accent };
        `;
  }, '' );
}

export const getCSSFromPalettes = ( palettes ) => {

  if ( ! palettes.length ) {
    return '';
  }

  // the old implementation generates 3 fallback palettes and
  // we need to overwrite all 3 of them when the user starts building a new palette
  // @todo this is necessary only in the Customizer preview
  while ( palettes.length < 3 ) {
    palettes.push( palettes[0] );
  }

  return palettes.reduce( ( palettesAcc, palette, paletteIndex, palettes ) => {
    let selector = `.sm-palette-${ paletteIndex }`;
    const { sourceIndex } = palette;
    const shiftedColors = getShiftedArray( palette.colors, sourceIndex );

    if ( paletteIndex === 0 ) {
      selector = `:root, ${ selector }`
    }

    return `
      ${ palettesAcc }
      
      ${ selector } { ${ getVariationVariablesCSS( palette.colors ) } }
      .sm-palette-${ paletteIndex }.sm-palette--shifted { ${ getVariationVariablesCSS( shiftedColors ) } }
    `;
  }, '');
}

const noop = palette => palette;
