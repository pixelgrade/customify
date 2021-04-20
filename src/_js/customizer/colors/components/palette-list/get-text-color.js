const getTextColor = ( preset ) => {
  const { palettes } = preset;

  if ( ! palettes.length ) {
    return [];
  }

  const palette = palettes[0];
  const { lightColorsCount, sourceIndex, textColors } = palette;

  return sourceIndex > lightColorsCount ? '#FFFFFF' : textColors[0].value;
}

export default getTextColor;
