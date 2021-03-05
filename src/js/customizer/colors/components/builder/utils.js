import { hexToHpluv, hpluvToRgb } from 'hsluv';
import chroma from 'chroma-js';

import contrastArray from './contrast-array';

export const getPalettesFromColors = ( colorGroups, attributes = {} ) => {
//  const functionalColors = getFunctionalColors( colorGroups );
  let palettes = colorGroups.map( mapColorToPalette( attributes ) );
//  let functionalPalettes = functionalColors.map( mapColorToPalette( attributes ) );

//  return mapSanitizePalettes( palettes.concat( functionalPalettes ), attributes );
  return mapSanitizePalettes( palettes, attributes );
}

export const addAutoPalettes = ( palettes, attributes ) => {

  if ( ! attributes.colorInterpolation ) {
    return palettes;
  }

  const newPalettes = JSON.parse( JSON.stringify( palettes ) );

  if ( newPalettes.length > 1 ) {
    const index0 = getBestPositionInPalette( newPalettes[0].source[0], newPalettes[0].colors, attributes );
    const index1 = getBestPositionInPalette( newPalettes[1].source[0], newPalettes[1].colors, attributes );
    let distance0 = Math.abs( index0 - index1 );
    let distance1 = 0;
    let distance2 = 0;

    if ( newPalettes.length > 2 ) {
      const index2 = getBestPositionInPalette( newPalettes[2].source[0], newPalettes[2].colors, attributes );
      distance1 = Math.abs( index1 - index2 );
      distance2 = Math.abs( index0 - index2 );
      const distance = Math.min( distance0, distance1, distance2 );

      if ( distance > 2 ) {
        const newPalette = createAutoPalette( newPalettes.slice( 0, 3 ), attributes );
        newPalettes.splice( 0, 3, newPalette );

        return newPalettes;
      }
    }

    if ( distance0 > 2 ) {
      const newPalette = createAutoPalette( [ newPalettes[0], newPalettes[1] ], attributes );
      newPalettes.splice( 0, 2, newPalette );

      return newPalettes;
    }

    if ( distance2 > 2 ) {
      const newPalette = createAutoPalette( [ newPalettes[0], newPalettes[2] ], attributes );
      newPalettes.splice( 0, 3, newPalette, newPalettes[1] );

      return newPalettes;
    }

    if ( distance1 > 2 ) {
      const newPalette = createAutoPalette( [ newPalettes[1], newPalettes[2] ], attributes );
      newPalettes.splice( 0, 3, newPalettes[0], newPalette );

      return newPalettes;
    }
  }

  return newPalettes;
}

export const mapSanitizePalettes = ( colors, attributes = {} ) => {
  return colors.map( mapCorrectLightness( attributes ) )
               .map( mapUpdateProps )
               .map( mapUseSource( attributes ) )
               .map( mapAddSourceIndex( attributes ) )
               .map( mapAddTextColors );
}

export const getFunctionalColors = ( colors ) => {

  if ( ! colors || ! colors.length ) {
    return [];
  }

  const color = colors[0].value;
  const red = chroma( color ).set( 'hsl.h', 0 ).hex();
  const blue = chroma( color ).set( 'hsl.h', 180 ).hex();
  const yellow = chroma( color ).set( 'hsl.h', 60 ).hex();
  const green = chroma( color ).set( 'hsl.h', 120 ).hex();

  return [
    { label: '_info', value: blue, id: 'info' },
    { label: '_error', value: red, id: 'error' },
    { label: '_warning', value: yellow, id: 'warning' },
    { label: '_success',  value: green, id: 'success' },
  ];
}

export const getSourceIndex = ( palette ) => {
  return palette.colors.findIndex( color => color.value === palette.source )
}

export const mapAddTextColors = ( palette ) => {
  palette.textColors = palette.colors.slice( 9, 11 ).map( ( color, index ) => {
    return {
      value: getTextColor( palette.source[0], index ),
      ...color
    }
  } );
  return palette;
}

export const mapAddSourceIndex = ( attributes ) => {

  return ( palette, index, palettes ) => {
    const { source, colors } = palette;
    let sourceIndex = getSourceIndex( palette );

    // falback sourceIndex when the source isn't used in the palette
    if ( ! sourceIndex > -1 ) {
      sourceIndex = getBestPositionInPalette( source[0], colors.map( color => color.value ), attributes );
    }

    return {
      sourceIndex,
      ...palette
    };
  }
}

export const mapColorToPalette = ( ( attributes ) => {

  return ( groupObject, index ) => {

    const colorObjects = groupObject.sources;
    const sources = colorObjects.map( colorObj => colorObj.value );
    const colors = createAutoPalette( sources, attributes );

    const { label, id } = colorObjects[0];

    return {
      id: id || ( index + 1 ),
      lightColorsCount: 5,
      label: label,
      source: sources,
      colors: colors,
    };
  }
} );

export const mapInterpolateSource = ( attributes ) => {
  const { mode } = attributes;

  return ( palette ) => {
    const { source } = palette;
    const position = getBestPositionInPalette( source[0], palette.colors, attributes );

    if ( mode !== 'none' ) {

      const stops = [
        { value: '#FFFFFF', position: 0 },
        { value: source[0], position: position },
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
      return chroma( color ).luminance( luminance, 'rgb' ).hex();
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
  const { useSources } = attributes;

  if ( ! useSources ) {
    return noop;
  }

  return ( palette ) => {
    const { source } = palette;
    const position = getBestPositionInPalette( source[0], palette.colors.map( color => color.value ), attributes );

    palette.colors.splice( position, 1, {
      value: source[0],
      isSource: true
    } );

    return palette;
  }
}

export const getBestPositionInPalette = ( color, colors, attributes, byColorDistance ) => {
  let min = Number.MAX_SAFE_INTEGER;
  let pos = -1;

  for ( let i = 0; i < colors.length - 1; i++ ) {
    let distance;

    if ( !! byColorDistance ) {
      distance = chroma.distance( colors[i], color, 'rgb' );
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

const contrastToLuminance = ( contrast ) => {
  return 1.05 / contrast - 0.05;
}

export const getVariablesCSS = ( palette, offset = 0, isDark = false, isShifted = false ) => {
  const { colors } = palette;
  const count = colors.length;

  return colors.reduce( ( colorsAcc, color, index ) => {
    let oldColorIndex = ( index + offset ) % count;

    if ( isDark ) {
      if ( oldColorIndex < count / 2 ) {
        oldColorIndex = 11 - oldColorIndex;
      } else {
        return colorsAcc;
      }
    }

    return `${ colorsAcc }
      ${ getColorVariables( palette, `${ index }`, oldColorIndex, isShifted ) }
    `;
  }, '' );
}

export const getInitialColorVaraibles = ( palette ) => {
  const { colors, textColors, id } = palette;
  const prefix = '--sm-color-palette-';

  let accentColors = colors.reduce( ( colorsAcc, color, index ) => {
    return `${ colorsAcc }
      ${ prefix }${ id }-color-${ index + 1 }: ${ color.value };
    `;
  }, '' );

  let darkColors = textColors.reduce( ( colorsAcc, color, index ) => {
    return `${ colorsAcc }
      ${ prefix }${ id }-text-color-${ index + 1 }: ${ color.value };
    `;
  }, '' );

  return `
    ${ accentColors }
    ${ darkColors }
  `;
}

export const getColorVariables = ( palette, newColorIndex, oldColorIndex, isShifted ) => {
  const { colors, id, lightColorsCount } = palette;
  const count = colors.length;
  const accentColorIndex = ( oldColorIndex + count / 2 ) % count;
  const prefix = '--sm-color-palette-';
  const suffix = isShifted ? '-shifted' : '';
  const newIndex = parseInt( newColorIndex, 10 ) + 1;

  let accentColors = `
    ${ prefix }${ id }-bg-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-color-${ oldColorIndex + 1 });
    ${ prefix }${ id }-accent-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-color-${ accentColorIndex + 1 });
  `;

  let darkColors = '';

  if ( oldColorIndex < lightColorsCount ) {
    darkColors = `
      ${ prefix }${ id }-fg1-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-text-color-1);
      ${ prefix }${ id }-fg2-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-text-color-2);
    `;
  } else {
    darkColors = `
      ${ prefix }${ id }-fg1-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-color-1);
      ${ prefix }${ id }-fg2-color-${ newIndex }${ suffix }: var(${ prefix }${ id }-color-1);
    `;
  }

  return `
    ${ accentColors }
    ${ darkColors }
  `;
}

export const getCSSFromPalettes = ( palettesArray ) => {

  const palettes = palettesArray.slice();

  if ( ! palettes.length ) {
    return '';
  }

  // the old implementation generates 3 fallback palettes and
  // we need to overwrite all 3 of them when the user starts building a new palette
  // @todo this is necessary only in the Customizer preview
  while ( palettes.length < 3 ) {
    palettes.push( palettes[0] );
  }

  const variationSetting = wp.customize( 'sm_site_color_variation' );
  const variation = !! variationSetting ? parseInt( variationSetting(), 10 ) : 1;

  return palettes.reduce( ( palettesAcc, palette, paletteIndex, palettes ) => {

    const { id, sourceIndex } = palette;

    return `
      ${ palettesAcc }
      
      html {
        ${ getInitialColorVaraibles( palette ) }
        ${ getVariablesCSS( palette, variation - 1 ) }
        ${ getVariablesCSS( palette, sourceIndex, false, true ) }
      } 
      
      .is-dark {
        ${ getVariablesCSS( palette, variation - 1, true ) }
        ${ getVariablesCSS( palette, sourceIndex, true, true ) }
      }
    `;
  }, '');
}

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

const createAutoPalette = ( colors, attributes = {} ) => {
  const { mode, bezierInterpolation } = attributes;
  const newColors = colors.slice();

  newColors.splice( 0, 0, '#FFFFFF' );
  newColors.push( '#000000' );
  newColors.sort( ( c1, c2 ) => {
    return chroma( c1 ).luminance() > chroma( c2 ).luminance() ? -1 : 1;
  } );

  if ( !! bezierInterpolation ) {
    return chroma.bezier( newColors ).scale().mode( mode ).correctLightness().colors( 12 );
  } else {
    return chroma.scale( newColors ).mode( mode ).correctLightness().colors( 12 );
  }
}

const noop = palette => palette;
