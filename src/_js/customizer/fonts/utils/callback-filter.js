import { standardizeNumericalValue } from './standardize-numerical-value';
import { round } from './round';

export const getCallbackFilter = ( connectedFieldData ) => {

  return ( newValue, oldValue ) => {

    /* ======================
     * Process the font logic to get the value that should be applied to the connected (font) fields.
     *
     * The font logic is already in the new value - @see setFieldFontsLogicConfig()
     */
    const newFontData = {};

    const fontsLogic = newValue;

    if ( typeof fontsLogic.reset !== 'undefined' ) {
      const settingID = connectedFieldData.setting_id;
      const defaultValue = customify.config.settings[settingID].default

      if ( !_.isUndefined( setting ) && !_.isEmpty( defaultValue ) ) {
        newFontData['font_family'] = defaultValue['font_family']
        newFontData['font_size'] = defaultValue['font_size']
        newFontData['line_height'] = defaultValue['line_height']
        newFontData['letter_spacing'] = defaultValue['letter_spacing']
        newFontData['text_transform'] = defaultValue['text_transform']
        newFontData['font_variant'] = defaultValue['font_variant']
      }
    }

    /* ===========
     * We need to determine the 6 subfields values to be able to determine the value of the font field.
     */

    // The font family is straight forward as it comes directly from the parent field font logic configuration.
    if ( typeof fontsLogic.font_family !== 'undefined' ) {
      newFontData['font_family'] = fontsLogic.font_family
    }

    if ( _.isEmpty( newFontData['font_family'] ) ) {
      // If we don't have a font family, we really can't do much.
      return
    }

    if ( typeof connectedFieldData.font_size !== 'undefined' && false !== connectedFieldData.font_size ) {
      newFontData['font_size'] = standardizeNumericalValue( connectedFieldData.font_size )

      // Next, we what to apply the overall font size multiplier.
      if ( !isNaN( newFontData['font_size'].value ) ) {
        // By default we use 1.
        let overallFontSizeMultiplier = 1.0
        if ( typeof fontsLogic.font_size_multiplier !== 'undefined' ) {
          // Make sure it is a positive float.
          overallFontSizeMultiplier = parseFloat( fontsLogic.font_size_multiplier )

          // We reject negative or 0 values.
          if ( overallFontSizeMultiplier <= 0 ) {
            overallFontSizeMultiplier = 1.0
          }
        }
        newFontData['font_size'].value = round( parseFloat( newFontData['font_size'].value ) * overallFontSizeMultiplier, customify.fonts.floatPrecision )
      }

      // The font variant, letter spacing and text transform all come together from the font styles (intervals).
      // We just need to find the one that best matches the connected field given font size (if given).
      // Please bear in mind that we expect the font logic styles to be preprocessed, without any overlapping and using numerical keys.
      if ( typeof fontsLogic.font_styles_intervals !== 'undefined' && _.isArray( fontsLogic.font_styles_intervals ) && fontsLogic.font_styles_intervals.length > 0 ) {
        let idx = 0
        while ( idx < fontsLogic.font_styles_intervals.length - 1 &&
                typeof fontsLogic.font_styles_intervals[idx].end !== 'undefined' &&
                fontsLogic.font_styles_intervals[idx].end <= connectedFieldData.font_size.value ) {

          idx ++
        }

        // We will apply what we've got.
        if ( !_.isEmpty( fontsLogic.font_styles_intervals[idx].font_variant ) ) {
          newFontData['font_variant'] = fontsLogic.font_styles_intervals[idx].font_variant
        }
        if ( !_.isEmpty( fontsLogic.font_styles_intervals[idx].letter_spacing ) ) {
          newFontData['letter_spacing'] = standardizeNumericalValue( fontsLogic.font_styles_intervals[idx].letter_spacing )
        }
        if ( !_.isEmpty( fontsLogic.font_styles_intervals[idx].text_transform ) ) {
          newFontData['text_transform'] = fontsLogic.font_styles_intervals[idx].text_transform
        }

        // Next, we what to apply the interval font size multiplier.
        if ( !isNaN( newFontData['font_size'].value ) ) {
          // By default we use 1.
          let fontSizeMultiplier = 1.0
          if ( typeof fontsLogic.font_styles_intervals[idx].font_size_multiplier !== 'undefined' ) {
            // Make sure it is a positive float.
            fontSizeMultiplier = parseFloat( fontsLogic.font_styles_intervals[idx].font_size_multiplier )

            // We reject negative or 0 values.
            if ( fontSizeMultiplier <= 0 ) {
              fontSizeMultiplier = 1.0
            }
          }

          newFontData['font_size'].value = round( parseFloat( newFontData['font_size'].value ) * fontSizeMultiplier, customify.fonts.floatPrecision )
        }
      }

      // The line height is determined by getting the value of the polynomial function determined by points.
      if ( typeof fontsLogic.font_size_to_line_height_points !== 'undefined' && _.isArray( fontsLogic.font_size_to_line_height_points ) ) {
        const result = regression.logarithmic( fontsLogic.font_size_to_line_height_points, {precision: customify.fonts.floatPrecision} )
        const lineHeight = result.predict( newFontData['font_size'].value )[1]
        newFontData['line_height'] = standardizeNumericalValue( lineHeight )
      }
    }

    return newFontData;
  }
}
