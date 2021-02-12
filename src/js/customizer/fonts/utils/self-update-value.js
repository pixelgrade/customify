import $ from "jquery";

import {
  fontsService,
  getFontDetails,
  standardizeNumericalValue,
} from './index';

/**
 * Gather the value for our entire font field and save it in the setting.
 */
export const selfUpdateValue = function( wrapper, settingID ) {
  // If we are already self-updating this and we haven't finished, we need to stop here to prevent infinite loops
  // This call might have come from a subfield detecting the change thus triggering a further selfUpdateValue()

  // If we are loading this setting value and haven't finished,
  // there is no point in updating it as this would cause infinite loops.
  if ( fontsService.isUpdating( settingID ) || fontsService.isLoading( settingID ) ) {
    return
  }

  // Mark the fact that we are self-updating the field value
  fontsService.setUpdating( settingID, true );

  const optionsList = wrapper.find( '.font-options__options-list' );
  const inputs = optionsList.find( '[data-value_entry]' );
  let newFontData = {};

  wp.customize( settingID, setting => {
    newFontData = $.extend( true, {}, setting() );

    inputs.each( function( key, input ) {
      const $input = $( input )
      const valueEntry = $input.data( 'value_entry' )
      let value = $input.val()

      // We only pick up subfields values that have been touched by the user, that are enabled (visible) or values that are missing in the oldValue.
      if ( _.isUndefined( valueEntry ) || $input.data( 'disabled' ) || (
        !$input.data( 'touched' ) && !_.isUndefined( newFontData[valueEntry] )
      ) ) {
        return
      }

      if ( 'font_family' === valueEntry ) {
        // Get the src of the selected option.
        const src = $( input.options[input.selectedIndex] ).data( 'src' )

        if ( src ) {
          newFontData['src'] = src
        } else {
          delete newFontData['src']
        }
      }

      if ( !_.isUndefined( value ) && !_.isNull( value ) && value !== '' ) {
        if ( _.includes( ['letter_spacing', 'line_height', 'font_size'], valueEntry ) ) {
          // Standardize the value.
          value = standardizeNumericalValue( value, input, false )
        }

        newFontData[valueEntry] = value
      } else {
        delete newFontData[valueEntry]
      }
    } )

    // We don't need to store font variants or subsets list in the value
    // since we will get those from the global font details.
    delete newFontData['variants']
    delete newFontData['subsets']

    // We need to make sure that we don't "use" any variants not supported by the new font (values passed over from the old value).
    // Get the new font details
    const newFontDetails = getFontDetails( newFontData['font_family'] )
    // Check the font variant
    if ( typeof newFontData['font_variant'] !== 'undefined' && typeof newFontDetails.variants !== 'undefined' && Object.keys( newFontDetails.variants ).length > 0 ) {
      // Make sure that the font_variant is a string, not a number.
      newFontData['font_variant'] = String( newFontData['font_variant'] )

      if ( !_.includes( newFontDetails.variants, newFontData['font_variant'] ) ) {
        // The new font doesn't have this variant. Nor should the value.
        delete newFontData['font_variant']
      }
    } else {
      // The new font has no variants. Nor should the value.
      delete newFontData['font_variant']
    }

    // Update the Customizer setting value.
    setting.set( newFontData );

  } );

  // Finished with the field value self-updating.
  fontsService.setUpdating( settingID, false );
}
