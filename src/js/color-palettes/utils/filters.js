import chroma from "chroma-js";

export const clarendon = ( hex, palette = [] ) => {
  const color = chroma( hex );

  // Color Group
  // Slightly increase saturation
  if ( palette.slice( 0, 3 ).some( x => x === hex ) ) {
    const saturation = color.get( 'hsl.s' );
    return color.set( 'hsl.s', saturation * 0.7 + 0.3 ).hex();
  }

  // Dark Group
  // Add dark to darker colors
  if ( palette.slice( 3, 6 ).some( x => x === hex ) ) {
    const lightness = color.get( 'hsl.l' );
    return color.set( 'hsl.l', lightness * 0.4 ).hex();
  }

  // Light Group
  // Add light to lighter colors
  if ( palette.slice( 6, 9 ).some( x => x === hex ) ) {
    const lightness = color.get( 'hsl.l' );
    return color.set( 'hsl.l', lightness * 0.6 + 0.4 ).hex();
  }

  return hex;
}

export const vivid = ( hex, palette ) => {
  const color = chroma( hex );
  const saturation = color.get( 'hsl.s' );

  return color.set( 'hsl.s', saturation * 0.5 + 0.5 ).hex();
}

export const softer = ( hex, palette ) => {
  let color = chroma( hex );
  let saturation = color.get( 'hsl.s' );
  let lightness = color.get( 'hsl.l' );

  color = color.set( 'hsl.s', saturation * 0.7 );
  color = color.set( 'hsl.l', lightness * 0.9 + 0.1 );

  return color.hex();
}

export const pastel = ( hex, palette ) => {
  let color = chroma( hex );
  let saturation = color.get( 'hsl.s' );
  let lightness = color.get( 'hsl.l' );

  color = color.set( 'hsl.s', saturation * 0.4 );
  color = color.set( 'hsl.l', lightness * 0.8 + 0.2 );

  return color.hex();
}

export const greyish = ( hex, palette ) => {
  const color = chroma( hex );
  const saturation = color.get( 'hsl.s' );
  return color.set( 'hsl.s', saturation * 0.2 ).hex();
}
