/** @namespace customify */
window.customify = window.customify || parent.customify || {};

(function ($, customify, wp, document) {

  /**
   * Expose the API publicly on window.customify.fontPalettes
   *
   * @namespace customify.fontPalettes
   */
  if ( typeof customify.fontPalettes === 'undefined' ) {
    customify.fontPalettes = {}
  }
  _.extend( customify.fontPalettes, function () {
    const api = wp.customize
    let apiSettings

    const initializePalettes = () => {
      // Cache initial settings configuration to be able to update connected fields on variation change.
      if (typeof customify.settingsClone === 'undefined') {
        customify.settingsClone = $.extend(true, {}, apiSettings)
      }

      // Create a stack of callbacks bound to parent settings to be able to unbind them
      // when altering the connected_fields attribute.
      if (typeof customify.fontPalettes.connectedFieldsCallbacks === 'undefined') {
        customify.fontPalettes.connectedFieldsCallbacks = {}
      }
    }

    const getConnectedFieldsCallback = function (parentSettingData, parentSettingID) {
      return function (newValue, oldValue) {
        _.each(parentSettingData.connected_fields, function (connectedFieldData) {
          /*
           * Create the value of the font field and set in the setting.
           */
          if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
            return
          }

          const setting = api(connectedFieldData.setting_id)
          if (_.isUndefined(setting)) {
            return
          }

          /* ======================
           * Process the font logic to get the value that should be applied to the connected (font) fields.
           *
           * The font logic is already in the new value - @see setFieldFontsLogicConfig()
           */
          const newFontData = {}

          const fontsLogic = newValue

          if (typeof fontsLogic.reset !== 'undefined') {
            const settingID = connectedFieldData.setting_id
            const defaultValue = customify.config.settings[settingID].default

            if (!_.isUndefined(setting) && !_.isEmpty(defaultValue)) {
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
          if (typeof fontsLogic.font_family !== 'undefined') {
            newFontData['font_family'] = fontsLogic.font_family
          }

          if (_.isEmpty(newFontData['font_family'])) {
            // If we don't have a font family, we really can't do much.
            return
          }

          if (typeof connectedFieldData.font_size !== 'undefined' && false !== connectedFieldData.font_size) {
            newFontData['font_size'] = customify.fontFields.standardizeNumericalValue(connectedFieldData.font_size)

            // Next, we what to apply the overall font size multiplier.
            if (!isNaN(newFontData['font_size'].value)) {
              // By default we use 1.
              let overallFontSizeMultiplier = 1.0
              if (typeof fontsLogic.font_size_multiplier !== 'undefined') {
                // Make sure it is a positive float.
                overallFontSizeMultiplier = parseFloat(fontsLogic.font_size_multiplier)

                // We reject negative or 0 values.
                if (overallFontSizeMultiplier <= 0) {
                  overallFontSizeMultiplier = 1.0
                }
              }
              newFontData['font_size'].value = round(parseFloat(newFontData['font_size'].value) * overallFontSizeMultiplier, customify.fonts.floatPrecision)
            }

            // The font variant, letter spacing and text transform all come together from the font styles (intervals).
            // We just need to find the one that best matches the connected field given font size (if given).
            // Please bear in mind that we expect the font logic styles to be preprocessed, without any overlapping and using numerical keys.
            if (typeof fontsLogic.font_styles_intervals !== 'undefined' && _.isArray(fontsLogic.font_styles_intervals) && fontsLogic.font_styles_intervals.length > 0) {
              let idx = 0
              while (idx < fontsLogic.font_styles_intervals.length - 1 &&
                  typeof fontsLogic.font_styles_intervals[idx].end !== 'undefined' &&
                  fontsLogic.font_styles_intervals[idx].end <= connectedFieldData.font_size.value) {

                idx++
              }

              // We will apply what we've got.
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].font_variant)) {
                newFontData['font_variant'] = fontsLogic.font_styles_intervals[idx].font_variant
              }
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].letter_spacing)) {
                newFontData['letter_spacing'] = customify.fontFields.standardizeNumericalValue(fontsLogic.font_styles_intervals[idx].letter_spacing)
              }
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].text_transform)) {
                newFontData['text_transform'] = fontsLogic.font_styles_intervals[idx].text_transform
              }

              // Next, we what to apply the interval font size multiplier.
              if (!isNaN(newFontData['font_size'].value)) {
                // By default we use 1.
                let fontSizeMultiplier = 1.0
                if (typeof fontsLogic.font_styles_intervals[idx].font_size_multiplier !== 'undefined') {
                  // Make sure it is a positive float.
                  fontSizeMultiplier = parseFloat(fontsLogic.font_styles_intervals[idx].font_size_multiplier)

                  // We reject negative or 0 values.
                  if (fontSizeMultiplier <= 0) {
                    fontSizeMultiplier = 1.0
                  }
                }

                newFontData['font_size'].value = round(parseFloat(newFontData['font_size'].value) * fontSizeMultiplier, customify.fonts.floatPrecision)
              }
            }

            // The line height is determined by getting the value of the polynomial function determined by points.
            if (typeof fontsLogic.font_size_to_line_height_points !== 'undefined' && _.isArray(fontsLogic.font_size_to_line_height_points)) {
              const result = regression.logarithmic(fontsLogic.font_size_to_line_height_points, {precision: customify.fonts.floatPrecision})
              const lineHeight = result.predict(newFontData['font_size'].value)[1]
              newFontData['line_height'] = customify.fontFields.standardizeNumericalValue(lineHeight)
            }
          }

          setting.set(newFontData)
        })
      }
    }

    const bindConnectedFields = function () {
      _.each(customify.fontPalettes.masterSettingIds, function (parentSettingID) {
        if (typeof apiSettings[parentSettingID] !== 'undefined') {
          const parentSettingData = apiSettings[parentSettingID]
          const parentSetting = api(parentSettingID)

          if (typeof parentSettingData.connected_fields !== 'undefined') {
            customify.fontPalettes.connectedFieldsCallbacks[parentSettingID] = getConnectedFieldsCallback(parentSettingData, parentSettingID)
            parentSetting.bind(customify.fontPalettes.connectedFieldsCallbacks[parentSettingID])
          }
        }
      })
    }

    const unbindConnectedFields = function () {
      _.each(customify.fontPalettes.masterSettingIds, function (parentSettingID) {
        if (typeof apiSettings[parentSettingID] !== 'undefined') {
          const parentSettingData = apiSettings[parentSettingID]
          const parentSetting = api(parentSettingID)

          if (typeof parentSettingData.connected_fields !== 'undefined' && typeof customify.fontPalettes.connectedFieldsCallbacks[parentSettingID] !== 'undefined') {
            parentSetting.unbind(customify.fontPalettes.connectedFieldsCallbacks[parentSettingID])
          }
          delete customify.fontPalettes.connectedFieldsCallbacks[parentSettingID]
        }
      })
    }

    // Alter connected fields of the master fonts controls depending on the selected palette variation.
    const reloadConnectedFields = () => {
      unbindConnectedFields()
      bindConnectedFields()
    }

    const onPaletteChange = function () {
      // Make sure that the advanced tab is visible.
      showAdvancedFontPaletteControls();

      // Take the fonts config for each setting and distribute it to each (master) setting.
      const data = $(this).data('fonts_logic')

      if (!_.isUndefined(data)) {
        $.each(data, function (settingID, config) {
          setFieldFontsLogicConfig(settingID, config)
        })
      }

      // In case this palette has values (options) attached to it, let it happen.
      $(this).trigger('customify:preset-change')
    }

    const setFieldFontsLogicConfig = function (settingID, config) {
      // We also need to trigger a fake setting value change since the master font controls don't usually hold a (usable) value.
      const setting = api(settingID)
      if (_.isUndefined(setting)) {
        return
      }

      // We will set the entire config as the master font field value just because it ensures us that,
      // when new info arrives, the setting callbacks will be fired (.set() doesn't do anything if the new value is the same as the old).
      // Also some entries will be used to set the master font subfields (mainly font family).
      // This value is not used in any other way!
      setting.set( config );
    }

    const handlePalettes = () => {
      // We need to do this here to be sure the data is available.
      apiSettings = api.settings.settings

      initializePalettes();
      reloadConnectedFields();

      // Handle the palette change logic.
      $('.js-font-palette input[name="sm_font_palette"]').on('change', onPaletteChange)

      // Handle the case where one clicks on the already selected palette - force a reset.
      $('.js-font-palette .customize-inside-control-row').on('click', function(event) {
        // Find the input
        let input = $(event.target).siblings('input')
        if (!input.length) {
          input = $(event.target).children('input')
        }

        // Only do the reset if the input is already checked (it's a radio group).
        if (input.length && input.prop('checked')) {
          // Take the fonts config for each setting and distribute it to each (master) setting.
          const data = input.data('fonts_logic')

          if (!_.isUndefined(data)) {
            $.each(data, function (settingID, config) {
              const setting = api(settingID)
              if (_.isUndefined(setting)) {
                return
              }

              // First set the setting to an empty value.
              // This is needed because the setting will not trigger a change if it is the same value.
              setting.set({})

              // Now set it's proper value.
              setFieldFontsLogicConfig(settingID, config)
            })
          }

          // In case this palette has values (options) attached to it, let it happen.
          input.trigger('customify:preset-change')
        }
      })

      // Handle the case when there is no selected font palette (like on a fresh installation without any demo data import).
      // In this case we want to hide the advanced tab.
      const currentFontPaletteSetting = api('sm_font_palette');
      if (typeof currentFontPaletteSetting === 'function' && '' === currentFontPaletteSetting()) {
        hideAdvancedFontPaletteControls();
      }
    }

    const hideAdvancedFontPaletteControls = () => {
      $('#sub-accordion-section-sm_font_palettes_section .sm-tabs__item[data-target="advanced"]').css('visibility', 'hidden');
    }

    const showAdvancedFontPaletteControls = () => {
      $('#sub-accordion-section-sm_font_palettes_section .sm-tabs__item[data-target="advanced"]').css('visibility', 'visible');
    }

    /**
     * Round a number to a precision, specified in number of decimal places
     *
     * @param {number} number - The number to round
     * @param {number} precision - The number of decimal places to round to:
     *                             > 0 means decimals, < 0 means powers of 10
     *
     *
     * @return {number} - The number, rounded
     */
    const round = function (number, precision) {
      const factor = Math.pow(10, precision)
      return Math.round(number * factor) / factor;
    }

    api.bind('ready', handlePalettes)

    return {}
  }() )

})(jQuery, customify, wp, document)
