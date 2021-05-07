const getRandomBetween = ( min, max ) => {
  const random = Math.max(0, Math.random() - Number.MIN_VALUE );
  return Math.floor( random * (max - min + 1) + min );
};

const getRandomStripes = ( preset => {
  const widths = [1, 1, 2, 2, 4];
  const { palettes } = preset;

  if ( ! palettes.length ) {
    return [];
  }

  const palette = palettes[0];
  const { sourceIndex } = palette;

  const stripes = Array.from( Array(5).keys() ).map( idx => {
    const stripe = document.createElement( 'div' );
    const widthPos = getRandomBetween( 0, widths.length - 1 );
    const width = widths[ widthPos ];

    widths.splice( widthPos, 1 );

    return {
      index: idx,
      element: stripe,
      width: width,
    }
  } );

  stripes.sort( ( a, b ) => a.width > b.width ? -1 : 1 );

  const segments = [ Array.from( Array(10).keys() ) ];

  stripes.forEach( stripe => {
    const segmentsIndexes = Array.from( Array( segments.length ).keys() );
    const availSegmentsIndexes = segmentsIndexes.filter( index => segments[ index ].length >= stripe.width );
    const segmentRandom = getRandomBetween( 0, availSegmentsIndexes.length - 1 );
    const segmentIndex = availSegmentsIndexes[segmentRandom];
    const thisSegment = segments[ segmentIndex ];
    const positionRandom = getRandomBetween( 0, thisSegment.length - stripe.width );
    const position = thisSegment[ positionRandom ];

    segments.splice( segmentIndex, 1, thisSegment.slice( 0, positionRandom ), thisSegment.slice( positionRandom + stripe.width, thisSegment.length ) );

    stripe.pos = position;
  } );

  const mainColor = palette.colors[ sourceIndex ].value;
  const colors = palettes.reduce( ( acc, palette ) => {
    const id = palette.id + '';
    const colorsToAdd = id.charAt(0) === '_' ? [] : palette.colors.map( color => color.value );

    return acc.concat( colorsToAdd );
  }, [] );

  stripes.forEach( stripe => {
    const colorIndex = getRandomBetween( 1, colors.length );
    const color = stripe.width === 4 ? mainColor : colors[ colorIndex ];
    colors.splice( colorIndex, 1 );
    stripe.color = color;
  } );

  stripes.sort( ( a, b ) => a.index > b.index ? -1 : 1 );

  return stripes;
} );

export default getRandomStripes;
