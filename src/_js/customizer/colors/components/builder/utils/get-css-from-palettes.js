export const getCSSFromPalettes = ( palettesArray, variation = 1 ) => {

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

const getVariablesCSS = ( palette, offset = 0, isDark = false, isShifted = false ) => {
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

const getInitialColorVaraibles = ( palette ) => {
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

const getColorVariables = ( palette, newColorIndex, oldColorIndex, isShifted ) => {
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
