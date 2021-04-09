import React, { useState } from 'react';

export const Preview = ( props ) => {

  const {
    palettes
  } = props;

  return palettes.map( ( palette, index ) => {
    const description = index === 0 ? 'Each column from the color palette below represent a state where a component could be. The first row is the main surface or background color, while the other two rows are for the content.' : '';

    return <PalettePreview key={ palette.id } palette={ { description, ...palette } } />
  } )
}

const PalettePreview = ( props ) => {
  const { palette } = props;
  const { colors, id } = palette;

  const [ current, setCurrent ] = useState( 2 );

  return (
    <div className={ `palette-preview sm-palette-${ id } sm-variation-${ current % 12 + 1 }` }>
      <div className={ `palette-preview-set` }>
        { colors.map( ( color, index ) => {

          let colorIndex = ( index + current + 10 ) % 12;
          let cardContent = null;
          let variation = colorIndex + 1;
          let modifier = '';

          if ( index === 2 ) {
            cardContent = [
              <h2 className="palette-preview-swatches__title">Text</h2>,
              <div className="palette-preview-swatches__body">
                <div className="palette-preview-swatches__row" />
                <div className="palette-preview-swatches__row" />
              </div>,
              <div className={ `palette-preview-swatches__button sm-variation-${ ( colorIndex + 6 ) % 12 + 1 }` }>&rarr;</div>
            ];
            modifier = 'current';
          }

          if ( index === 8 ) {
            modifier = 'accent';
          }

          if ( index === 10 ) {
            modifier = 'text';
            variation = ( current % 12 ) + 1;
          }

          let className = `palette-preview-swatches ${ ! modifier ? '' : `palette-preview-swatches--${ modifier }` } sm-variation-${ variation }`;

          return (
            <div key={ index } className={ className } onClick={ () => { setCurrent( colorIndex ) } }>
              <div className={ `palette-preview-swatches__card` }>
                <div className={ `palette-preview-swatches__card-top` } />
                <div className={ `palette-preview-swatches__card-content` }>
                  { cardContent }
                </div>
                <div className={ `palette-preview-swatches__card-bottom` } />
              </div>
            </div>
          )
        } ) }
      </div>
    </div>
  )
}
