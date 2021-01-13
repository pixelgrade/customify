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
               .map( mapAddTextColor( 9, attributes ) )
               .map( mapAddTextColor( 10, attributes ) );
} );

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

const mapAddTextColor = ( position, attributes ) => {
  const { mode } = attributes;
  const luminance = contrastToLuminance( contrastArray[ position ] );

  return ( palette ) => {
    const { source } = palette;
    const hpluv = hexToHpluv( source );
    const h = Math.min( Math.max( hpluv[0], 0), 360 );
    const p = Math.min( Math.max( hpluv[1], 0), 100 );
    const l = Math.min( Math.max( hpluv[2], 0), 100 );
    const rgb = hpluvToRgb( [h, p, l] ).map( x => Math.max(0, Math.min( x * 255, 255 ) ) )
    const textColor = chroma( rgb ).luminance( luminance, mode ).hex();

    palette.colors.push( { value: textColor } );

    return palette;
  }
}

const contrastToLuminance = ( contrast ) => {
  return 1.05 / contrast - 0.05;
}

const noop = palette => palette;
