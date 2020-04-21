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
          if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id) || _.isUndefined(parentSettingData.fonts_logic)) {
            return
          }

          const setting = api(connectedFieldData.setting_id)
          if (_.isUndefined(setting)) {
            return
          }

          /* ======================
           * Process the font logic for the master (parent) font control to get the value that should be applied to the connected (font) fields.
           */
          const newFontData = {}
          const fontsLogic = parentSettingData.fonts_logic

          // @todo Is this still in use? Can't find the logic that triggers it.
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

          // The font type is determined on the fly, ignoring anything that may be set in the parent font logic configuration.
          newFontData['type'] = customify.fontFields.determineFontType(newFontData['font_family'])

          // The selected variants also come straight from the font logic right now.
          if (typeof fontsLogic.font_weights !== 'undefined') {
            newFontData['variants'] = fontsLogic.font_weights
          }

          if (typeof connectedFieldData.font_size !== 'undefined' && false !== connectedFieldData.font_size) {
            newFontData['font_size'] = connectedFieldData.font_size

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
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].font_weight)) {
                newFontData['font_variant'] = fontsLogic.font_styles_intervals[idx].font_weight
              }
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].letter_spacing)) {
                newFontData['letter_spacing'] = fontsLogic.font_styles_intervals[idx].letter_spacing
              }
              if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].text_transform)) {
                newFontData['text_transform'] = fontsLogic.font_styles_intervals[idx].text_transform
              }
            }

            // The line height is determined by getting the value of the polynomial function determined by points.
            if (typeof fontsLogic.font_size_to_line_height_points !== 'undefined' && _.isArray(fontsLogic.font_size_to_line_height_points)) {
              const result = regression.logarithmic(fontsLogic.font_size_to_line_height_points, {precision: 2})
              const fontsize = connectedFieldData.font_size.value
              const lineHeight = result.predict(fontsize)[1]
              newFontData['line_height'] = {value: lineHeight}
            }
          }

          const serializedNewFontData = customify.fontFields.encodeValues(newFontData)
          setting.set(serializedNewFontData)
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
      apiSettings[settingID].fonts_logic = config

      // We also need to trigger a fake setting value change since the master font controls don't usually hold a (usable) value.
      const setting = api(settingID)
      if (_.isUndefined(setting)) {
        return
      }

      // We will set the entire config as the master font field value just because it ensures us that,
      // when new info arrives, the setting callbacks will be fired (.set() doesn't do anything if the new value is the same as the old).
      // Also some entries will be used to set the master font subfields (mainly font family).
      // This value is not used in any other way!
      const serializedNewFontData = customify.fontFields.encodeValues(config)
      setting.set(serializedNewFontData)
    }

    const handlePalettes = () => {
      // We need to do this here to be sure the data is available.
      apiSettings = api.settings.settings

      initializePalettes()
      reloadConnectedFields()

      $(document).on('click', '.js-font-palette input', onPaletteChange)
    }

    api.bind('ready', handlePalettes)

    return {}
  }() )

})(jQuery, customify, wp, document)
