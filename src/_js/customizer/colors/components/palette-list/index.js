import React, { useState } from 'react';
import getRandomStripes from './get-random-stripes';
import presets from './presets';

import './style.scss';
import { getPalettesFromColors } from "../builder";

presets.forEach( ( preset ) => {
  preset.palettes = getPalettesFromColors( preset.config );
  preset.stripes = getRandomStripes( preset );
} );

const PresetsList = ( props ) => {

  const noop = () => {};
  const onChange = props.onChange || noop;

  return (
    <div className={ 'sm-presets-list' }>
      {
        presets.map( preset => {
          return (
            <PaletteListItem preset={ preset } key={ preset.uid } active={ preset.uid === props.active } onChange={ onChange } />
          );
        } )
      }
    </div>
  )
}

const PaletteListItem = ( props ) => {

  const { preset, active } = props;
  const { stripes, quote, image } = preset;

  const noop = () => {};
  const onChange = props.onChange || noop;

  return (
    <div className={ `sm-presets-list__item` } onClick={ () => { onChange( preset ) } }>
      <div className={ `sm-presets-preview ${ active ? 'sm-presets-preview--active' : '' }` } style={ { backgroundImage: `url(${ image })` } }>
        <div className="sm-presets-preview__quote">{ quote }</div>
        <div className="sm-presets-preview__stripes">
          { stripes.map( stripe => {
            return (
              <div className={ `sm-presets-preview__stripe sm-presets-preview__stripe-w${ stripe.width } sm-presets-preview__stripe-p${ stripe.pos }` }>
                <div className="sm-presets-preview__pixel" style={ { color: stripe.color } } />
              </div>
            );
          } ) }
        </div>
      </div>
    </div>
  );
}

export default PresetsList;
