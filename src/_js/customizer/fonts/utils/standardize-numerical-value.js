import $ from "jquery";

/**
 * Given a value we will standardize it to an array with 'value' and 'unit'.
 *
 * This is a mirror logic of the server-side one from Utils\Fonts::standardizeNumericalValue()
 *
 * @param value
 * @param input Optional. The input this value was extracted from
 * @param valueFirst Optional. Whether to give higher priority to value related data, or to input related one.
 */
export const standardizeNumericalValue = function( value, input = false, valueFirst = true ) {
  const standardValue = {value: false, unit: false}

  if ( _.includes( ['', 'false', false], value ) ) {
    return standardValue
  }

  if ( !isNaN( value ) ) {
    standardValue.value = value
  } else if ( typeof value.value !== 'undefined' ) {
    standardValue.value = value.value
    if ( typeof value.unit !== 'undefined' ) {
      standardValue.unit = value.unit
    }
  } else if ( typeof value[0] !== 'undefined' ) {
    standardValue.value = value[0]
    if ( typeof value[1] !== 'undefined' ) {
      standardValue.unit = value[1]
    }
  } else if ( typeof value === 'string' ) {
    // We will get everything in front that is a valid part of a number (float including).
    const matches = value.match( /^([\d.\-+]+)(.+)/i )
    if ( matches !== null && typeof matches[1] !== 'undefined' ) {
      standardValue.value = matches[1]
      if ( !_.isEmpty( matches[2] ) ) {
        standardValue.unit = matches[2]
      }
    } else {
      // If we could not extract anything useful we will trust the developer and leave it like that.
      standardValue.value = value
    }
  }

  if ( false !== input && (
    false === standardValue.unit || _.isEmpty( standardValue.unit )
  ) ) {
    // If we are given an input, we will attempt to extract the unit from its attributes.
    let fallbackInputUnit = ''
    const $input = $( input )

    if ( valueFirst ) {
      if ( !_.isEmpty( $input.data( 'value_unit' ) ) ) {
        fallbackInputUnit = $input.data( 'value_unit' )
      } else if ( !_.isEmpty( $input.attr( 'unit' ) ) ) {
        fallbackInputUnit = $input.attr( 'unit' )
      }
    } else {
      if ( !_.isEmpty( $input.attr( 'unit' ) ) ) {
        fallbackInputUnit = $input.attr( 'unit' )
      } else if ( !_.isEmpty( $input.data( 'value_unit' ) ) ) {
        fallbackInputUnit = $input.data( 'value_unit' )
      }
    }
    standardValue.unit = fallbackInputUnit
  }

  // Make sure that if we have a numerical value, it is a float.
  if ( !isNaN( standardValue.value ) ) {
    standardValue.value = parseFloat( standardValue.value );
  }

  return standardValue
}
