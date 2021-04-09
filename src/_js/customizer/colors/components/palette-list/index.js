import './style.scss';

const getRandomBetween = ( min, max ) => {
  const random = Math.max(0, Math.random() - Number.MIN_VALUE );
  return Math.floor( random * (max - min + 1) + min );
};

const randomize = ( palette => {
  const widths = [1, 1, 2, 2, 4];
  const { colors, sourceIndex } = palette;

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

  stripes.forEach( stripe => {
    const colorIndex = stripe.width === 4 ? sourceIndex : getRandomBetween( 1, colors.length );
    const color = colors[ colorIndex ];
    colors.splice( colorIndex, 1 );
    stripe.color = color;
  } );

  stripes.sort( ( a, b ) => a.index > b.index ? -1 : 1 );

  return stripes;
} );

const PaletteList = ( props ) => {

  const palettes = [ {
    sourceIndex: 2,
    colors: [ "#ffffff", "#fff8d0", "#fae695", "#e8bb4f", "#d09434", "#af722b", "#985e2d", "#7f4e32", "#5f3928", "#4f2e1f", "#39190a", "#101010" ],
    image: 'https://images.unsplash.com/photo-1564107628966-daff03746bee?ixid=MXwxMjA3fDB8MHxzZWFyY2h8MjV8fGRlc2VydHxlbnwwfHwwfA%3D%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
  }, {
    sourceIndex: 5,
    colors: [ "#ffffff", "#fff9c3", "#ffe496", "#ffb15f", "#fc7858", "#db4f59", "#bc3d65", "#95366b", "#6d3266", "#442c58", "#000c2a", "#101010" ],
    image: 'https://images.unsplash.com/photo-1579426922688-8b4607285cd2?ixid=MXwxMjA3fDB8MHxzZWFyY2h8NDh8fHN1bnNldHxlbnwwfHwwfA%3D%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
  }, {
    sourceIndex: 2,
    colors: [ "#ffffff", "#f6fcc3", "#e2ed92", "#b6cc43", "#8fab21", "#6d8912", "#6b7100", "#785206", "#7a320c", "#6d0000", "#470b0b", "#101010" ],
    image: 'https://images.unsplash.com/photo-1600626333392-59a20e646d97?ixid=MXwxMjA3fDB8MHxzZWFyY2h8Nnx8YXBwbGVzfGVufDB8fDB8&ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
  } ];

  return (
    <div className={ 'sm-palette-list' }>
      { palettes.map( palette => {
        const stripes = randomize( palette );
        return (
          <div className="sm-palette-list__item">
            <div className="sm-palette-preview" style={ { backgroundImage: `url(${ palette.image })` } }>
              <div className="sm-palette-preview__stripes">
                { stripes.map( stripe => {
                  return (
                    <div className={ `sm-palette-preview__stripe sm-palette-preview__stripe-w${ stripe.width } sm-palette-preview__stripe-p${ stripe.pos }` }>
                      <div className="sm-palette-preview__pixel" style={ { color: stripe.color } } />
                    </div>
                  );
                } ) }
              </div>
            </div>
          </div>
        )
      } ) }
    </div>
  )
}

export default PaletteList;
