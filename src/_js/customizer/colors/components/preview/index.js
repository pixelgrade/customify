import classnames from 'classnames';
import React, { Fragment, useEffect, useState } from 'react';

import './style.scss';

const Preview = ( props ) => {

  return (
    <Fragment>
      <div className={ `palette-preview-header sm-palette-1 sm-palette--shifted sm-variation-1` }>
        <div className={ `sm-overlay__wrap` }>
          <div className={ `sm-overlay__container` }>
            <div className={ `palette-preview-header-wrap` }>
              <h1 className={ `palette-preview-title` }>The color system</h1>
              <p className={ `palette-preview-description` }>The color system generated it’s based on your brands color and a set of underlying principles and guidelines, making color usage accessible and purposeful.</p>
            </div>
          </div>
        </div>
      </div>
      <PalettePreviewList { ...props } />
    </Fragment>
  )
}

const PalettePreviewList = ( props ) => {

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
  const [ lastHover, setLastHover ] = useState( 0 );

  const siteVariationSetting = wp.customize( 'sm_site_color_variation' );
  const [ siteVariation, setSiteVariation ] = useState( parseInt( siteVariationSetting(), 10 ) );

  const onSiteVariationChange = ( newValue ) => {
    setSiteVariation( parseInt( newValue, 10 ) );
  }

  useEffect( () => {
    if ( hover !== false ) {
      setLastHover( hover );
    }
  }, [ hover ] );

  useEffect( () => {
    // Attach the listeners on component mount.
    siteVariationSetting.bind( onSiteVariationChange );

    // Detach the listeners on component unmount.
    return () => {
      siteVariationSetting.unbind( onSiteVariationChange );
    }
  }, [] );

  const normalize = index => {
    return ( index + siteVariation - 1 + 12 ) % 12;
  }

  return (
    <div className={ `palette-preview sm-palette-${ id } ${ lastHover !== false ? `sm-variation-${ lastHover + 1 }` : '' }` }>
      <div className={ `sm-overlay__wrap` }>
        <div className={ `sm-overlay__container` }>
          <div className={ `palette-preview-set` }>
            { colors.map( ( color, index ) => {

              const variation = index + 1;
              const showLightForeground = normalize( index ) === 0;
              const showDarkForeground = normalize( index ) === 9;
              const foregroundToShow = normalize( hover ) >= lightColorsCount ? showLightForeground : showDarkForeground;

              const passedProps = {
                showCard: index === hover,
                showAccent: ( hover !== false ) && ( index === ( hover + 6 ) % 12 ),
                showForeground: ( hover !== false ) && foregroundToShow,
                textColor: normalize( index ) >= lightColorsCount ? textColors[0].value : '#FFFFFF',
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
        <div className="palette-preview-swatches__text">Surface</div>
        <PalettePreviewGradeCard variation={ variation } />
      </div>
      <div className="palette-preview-swatches__wrap-background" style={ { color: 'var(--sm-current-bg-color)' } } />
      <div className="palette-preview-swatches__wrap-accent" style={ { color: 'var(--sm-current-bg-color)' } }>
        <div className="palette-preview-swatches__text">Accent</div>
      </div>
      <div className="palette-preview-swatches__wrap-foreground"  style={ { color: textColor } }>
        <div className="palette-preview-swatches__text">Text</div>
      </div>
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
