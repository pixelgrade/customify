import { handleColorSelectFields } from './fields/color-select';
import { handleAceEditors } from './fields/ace-editor';
import { handleRangeFields } from './fields/range';

import { handleFoldingFields } from './folding-fields';
import { scalePreview } from './scale-preview';
import { createResetButtons } from './reset';

wp.customize.bind( 'ready', () => {

  handleRangeFields();
  handleColorSelectFields();
  handleAceEditors(); // could be removed

  // @todo check reason for this timeout
  setTimeout( function () {
    handleFoldingFields();
  }, 1000 );

  scalePreview();

} );
