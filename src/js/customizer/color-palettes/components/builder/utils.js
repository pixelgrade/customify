import { hexToHpluv, hpluvToRgb } from 'hsluv';
import chroma from 'chroma-js';

import contrastArray from './contrast-array';

const attributes = {
  correctLightness: true,
  useSources: true,
  mode: 'hsl',
}

export const getPalettesFromColors = ( colors => {

  return colors.concat( getFunctionalColors( colors ) )
               .map( mapColorToPalette( attributes ) )
               .map( mapInterpolateSource( attributes ) )
               .map( mapCorrectLightness( attributes ) )
               .map( mapUpdateProps )
               .map( mapUseSource( attributes ) )
               .map( mapAddSourceIndex )
               .map( mapAddTextColors );
} );

export const getFunctionalColors = ( colors ) => {
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
      value: getTextColor( palette.source, index ),
      ...color
    }
  } );
  return palette;
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

export const mapColorToPalette = ( ( attributes ) => {
  const { mode } = attributes;

  return ( colorObj, index ) => {
    const { label, id, value } = colorObj;
    const reference = chroma( value ).set( 'hsv.s', 1 ).set( 'hsv.v', 1 ).hex();

    const colors = contrastArray.map( contrast => {
      const luminance = contrastToLuminance( contrast );
      return chroma( value ).luminance( luminance, mode ).hex();
    } );

    return {
      id: id || index,
      label: label,
      source: value,
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

const contrastToLuminance = ( contrast ) => {
  return 1.05 / contrast - 0.05;
}

export const getVariablesCSS = ( palette, offset = 0, isDark = false, prefix = false ) => {
  const { colors, textColors, id, sourceIndex } = palette;
  const count = colors.length;

  return colors.reduce( ( colorsAcc, color, index ) => {
    let oldColorIndex = ( index + offset ) % count;

    if ( isDark ) {
      if ( oldColorIndex < count / 2 ) {
        oldColorIndex = 11 - oldColorIndex;
      } else {
        return `${ colorsAcc }`;
      }
    }

    return `${ colorsAcc }
      ${ getColorVaraibles( palette, index, oldColorIndex, prefix ) }
    `;
  }, '' );
}

export const getInitialColorVaraibles = ( palette ) => {
  const { colors, textColors, id } = palette;

  let accentColors = colors.reduce( ( colorsAcc, color, index ) => {
    return `${ colorsAcc }
      --sm-${ id }-color-${ index }: ${ color.value };
    `;
  }, '' );

  let darkColors = textColors.reduce( ( colorsAcc, color, index ) => {
    return `${ colorsAcc }
      --sm-${ id }-text-color-${ index }: ${ color.value };
    `;
  }, '' );

  return `
    ${ accentColors }
    ${ darkColors }
  `;
}

export const getColorVaraibles = ( palette, newColorIndex, oldColorIndex, prefix ) => {
  const { colors, textColors } = palette;
  const count = colors.length;
  const accentColorIndex = ( oldColorIndex + count / 2 ) % count;
  const id = prefix || palette.id;

  let accentColors = `
    --sm-${ id }-background-color-${ newColorIndex }: var(--sm-${ id }-color-${ oldColorIndex });
    --sm-${ id }-accent-color-${ newColorIndex }: var(--sm-${ id }-color-${ accentColorIndex });
  `;

  let darkColors = '';

  if ( oldColorIndex < 6 ) {
    darkColors = `
      --sm-${ id }-dark-color-${ newColorIndex }: var(--sm-${ id }-text-color-0);
      --sm-${ id }-darker-color-${ newColorIndex }: var(--sm-${ id }-text-color-1);
    `;
  } else {
    darkColors = `
      --sm-${ id }-dark-color-${ newColorIndex }: var(--sm-${ id }-color-0);
      --sm-${ id }-darker-color-${ newColorIndex }: var(--sm-${ id }-color-0);
    `;
  }

  return `
    ${ accentColors }
    ${ darkColors }
  `;
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

  const variationSetting = wp.customize( 'sm_site_color_variation' );
  const variation = !! variationSetting ? variationSetting() : 0;

  return palettes.reduce( ( palettesAcc, palette, paletteIndex, palettes ) => {

    const { id, sourceIndex } = palette;

    return `
      ${ palettesAcc }
      
      html {
        ${ getInitialColorVaraibles( palette ) }
        ${ getVariablesCSS( palette, variation ) }
        ${ getVariablesCSS( palette, sourceIndex, false, `${ id }-shifted` ) }
      } 
      
      html.is-dark {
        ${ getVariablesCSS( palette, variation, true ) }
        ${ getVariablesCSS( palette, sourceIndex, true, `${ id }-shifted` ) }
      }
    `;
  }, '');
}

const noop = palette => palette;
