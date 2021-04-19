import classnames from 'classnames';
import React, { Fragment, useState } from 'react';

const Preview = ( props ) => {

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
  const { id, colors, textColors, lightColorsCount } = palette;
  const [ hover, setHover ] = useState(false );

  return (
    <div className={ `palette-preview sm-palette-${ id }` }>
      <div className={ `palette-preview-set` }>
        { colors.map( ( color, index ) => {

          const variation = index + 1;

          const passedProps = {
            showCard: index === hover,
            showAccent: ( hover !== false ) && ( index === ( hover + 6 ) % 12 ),
            showForeground: ( hover !== false ) && ( hover > lightColorsCount ? index === 0 : index === 9 ),
            textColor: index > lightColorsCount ? textColors[0].value : '#FFFFFF',
            variation,
          }

          return (
            <div key={ index } className={ `palette-preview-swatches sm-variation-${ variation }` }
                 onMouseEnter={ () => { setHover( index ) } }
                 onMouseLeave={ () => { setHover( false ) } }>
              <PalettePreviewGrade { ...passedProps } />
            </div>
          )
        } ) }
      </div>
    </div>
  )
}

const PalettePreviewGrade = ( props ) => {

  const {
    showCard,
    showAccent,
    showForeground,
    textColor,
    variation,
  } = props;

  const className = classnames(
    'palette-preview-swatches__wrap',
    {
      'show-card': showCard,
      'show-accent': showAccent,
      'show-fg': showForeground,
    }
  )

  return (
    <div className={ className }>
      <div className="palette-preview-swatches__wrap-surface">
        <PalettePreviewGradeCard variation={ variation } />
      </div>
      <div className="palette-preview-swatches__wrap-background" style={ { color: 'var(--sm-current-bg-color)' } } />
      <div className="palette-preview-swatches__wrap-accent" style={ { color: 'var(--sm-current-bg-color)' } } />
      <div className="palette-preview-swatches__wrap-foreground"  style={ { color: textColor } } />
    </div>
  );
}

const PalettePreviewGradeCard = ( props ) => {

  const { variation } = props;
  const buttonVariation = ( variation - 1 + 6 ) % 12 + 1;

  return (
    <div className={ `palette-preview-swatches__card` }>
      <div className={ `palette-preview-swatches__card-content` }>
        <h2 className="palette-preview-swatches__title">Text</h2>
        <div className="palette-preview-swatches__body">
          <div className="palette-preview-swatches__row" />
          <div className="palette-preview-swatches__row" />
        </div>
        <div className={ `palette-preview-swatches__button sm-variation-${ buttonVariation }` }>&rarr;</div>
      </div>
    </div>
  )
}

export default Preview;
