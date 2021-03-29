import $ from "jquery";

import {
  getSettingID,
  getWrapper,
  selfUpdateValue,
} from './index';

export const initSubfield = function( $subField, select2 = false ) {

  // Mark these as not touched by the user.
  $subField.data( 'touched', false )

  $subField.on( 'input change', onSubfieldChange )

  // If we've been instructed, initialize a select2.
  if ( true === select2 ) {
    $subField.select2();
  }
}

const onSubfieldChange = ( event, who ) => {
  const $subField = $( event.target );

  // The change was triggered programmatically by Customify.
  // No need to self-update the value.
  if ( 'customify' === who ) {
    return;
  }

  const wrapper = getWrapper( $subField );
  const settingID = getSettingID( $subField );

  // Mark this input as touched by the user.
  $subField.data( 'touched', true );

  // Gather subfield values and trigger refresh of the fonts in the preview window.
  selfUpdateValue( wrapper, settingID );
}
