import $ from "jquery";
import { fontsService } from './index';
import { round } from './round';
import { standardizeNumericalValue } from './standardize-numerical-value';

/**
 * This function is a reverse of selfUpdateValue(), initializing the entire font field controls
 * based on the setting value.
 */
export const loadFontValue = function( wrapper, value, settingID ) {
  // If we are already loading this setting value and haven't finished, there is no point in starting again.
  if ( fontsService.isLoading( settingID ) ) {
    return
  }

  // Mark the fact that we are loading the field value
  fontsService.setLoading( settingID, true );

  const optionsList = $( wrapper ).find( '.font-options__options-list' ),
    inputs = optionsList.find( '[data-value_entry]' )

  inputs.each( function( key, input ) {
    const $input = $( input )
    const valueEntry = $input.data( 'value_entry' )

    // In the case of select2, only the original selects have the data field, thus excluding select2 created select DOM elements
    if ( typeof valueEntry === 'undefined' || valueEntry === '' || typeof value[valueEntry] === 'undefined' ) {
      return
    }

    // We will do this only for numerical sub-fields.
    if ( _.includes( ['letter_spacing', 'line_height', 'font_size'], valueEntry ) ) {
      const subfieldValue = standardizeNumericalValue( value[valueEntry], input )

      // Make sure that the unit and value_unit attributes are in place.
      if ( subfieldValue.unit !== '' ) {
        $input.data( 'value_unit', subfieldValue.unit )
        if ( _.isEmpty( $input.attr( 'unit' ) ) ) {
          $input.attr( 'unit', subfieldValue.unit )
        }
      }

      // If the field unit and value unit differ, we have some conversion to do.
      // We will convert the received value to the appropriate unit declared by the input.
      // We will use a guessed base size of 16px. Not an exact conversion, but it will have to do.
      const baseSize = 16
      const subfieldUnit = $input.attr( 'unit' ).trim().toLowerCase()
      const subfieldValueUnit = $input.data( 'value_unit' ).trim().toLowerCase()
      // The comparison is intentionally loose.
      if ( subfieldUnit != subfieldValueUnit ) {
        if ( _.includes( ['em', 'rem'], subfieldValueUnit ) && 'px' === subfieldUnit ) {
          // We will have to multiply the value.
          subfieldValue.value = round( subfieldValue.value * baseSize, customify.fonts.floatPrecision )
        } else if ( _.includes( ['em', 'rem'], subfieldUnit ) && 'px' === subfieldValueUnit ) {
          // We will have to divide the value.
          subfieldValue.value = round( subfieldValue.value / baseSize, customify.fonts.floatPrecision )
        }
      }

      // If this field has a min/max attribute we need to make sure that those attributes allow for the value we are trying to impose.
      if ( $input.attr( 'min' ) && $input.attr( 'min' ) > subfieldValue.value ) {
        $input.attr( 'min', subfieldValue.value )
      }
      if ( $input.attr( 'max' ) && $input.attr( 'max' ) < subfieldValue.value ) {
        $input.attr( 'max', subfieldValue.value )
      }

      $input.val( subfieldValue.value )
    } else {
      $input.val( value[valueEntry] )
    }

    // Mark this input as not touched by the user.
    $input.data( 'touched', false )

    $input.trigger( 'change', ['customify'] )
  } )

  // Finished with the field value loading.
  fontsService.setLoading( settingID, false );
}
