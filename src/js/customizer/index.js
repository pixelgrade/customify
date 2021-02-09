import $ from 'jquery';
import './style.scss';

import { handleColorSelectFields } from './fields/color-select';
import { handleRangeFields } from './fields/range';

import { handleFoldingFields } from './folding-fields';
import { scalePreview } from './scale-preview';
import { createResetButtons } from './reset';

wp.customize.bind( 'ready', () => {

  handleRangeFields();
  handleColorSelectFields();

  // @todo check reason for this timeout
  setTimeout( function () {
    handleFoldingFields();
  }, 1000 );

  // Initialize simple select2 fields.
  $( '.customify_select2' ).select2();

  // Initialize font fields.
  customify.fontFields.init();

  scalePreview();

} );
