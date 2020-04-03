let FontPalettes = (function ($, exports, wp) {
  const api = wp.customize
  let apiSettings

  const masterSettingIds = [
    'sm_font_primary',
    'sm_font_secondary',
    'sm_font_body',
    'sm_font_accent',
  ]

  const defaultFontType = 'google'

  const initializePalettes = () => {
    // Cache initial settings configuration to be able to update connected fields on variation change.
    if (typeof window.settingsClone === 'undefined') {
      window.settingsClone = $.extend(true, {}, apiSettings)
    }

    // Create a stack of callbacks bound to parent settings to be able to unbind them
    // when altering the connected_fields attribute.
    if (typeof window.fontsConnectedFieldsCallbacks === 'undefined') {
      window.fontsConnectedFieldsCallbacks = {}
    }
  }

  const getConnectedFieldsCallback = function (parent_setting_data, parent_setting_id) {
    return function (new_value, old_value) {
      _.each(parent_setting_data.connected_fields, function (connected_field_data) {
        if (_.isUndefined(connected_field_data) || _.isUndefined(connected_field_data.setting_id) || !_.isString(connected_field_data.setting_id) || _.isUndefined(parent_setting_data.fonts_logic)) {
          return
        }

        let setting = api(connected_field_data.setting_id)
        if (_.isUndefined(setting)) {
          return
        }

        /* ======================
         * Process the font logic for the master (parent) font control to get the value that should be applied to the connected (font) fields.
         */
        let newFontData = {}
        let fonts_logic = parent_setting_data.fonts_logic
        let serializedNewFontData

        // @todo Is this still in use? Can't find the logic that triggers it.
        if (typeof fonts_logic.reset !== 'undefined') {
          let setting_id = connected_field_data.setting_id
          let defaultValue = customify_settings.settings[setting_id].default

          if (!_.isUndefined(setting) && !_.isEmpty(defaultValue)) {
            newFontData['font_family'] = defaultValue['font_family']
            newFontData['font_size'] = defaultValue['font_size']
            newFontData['line_height'] = defaultValue['line_height']
            newFontData['letter_spacing'] = defaultValue['letter_spacing']
            newFontData['text_transform'] = defaultValue['text_transform']
            newFontData['selected_variants'] = defaultValue['selected_variants']
          }
        }

        /* ===========
         * We need to determine the 6 subfields values to be able to determine the value of the font field.
         */

        // The font family is straight forward as it comes directly from the parent field font logic configuration.
        if (typeof fonts_logic.font_family !== 'undefined') {
          newFontData['font_family'] = fonts_logic.font_family
        }

        if (_.isEmpty(newFontData['font_family'])) {
          // If we don't have a font family, we really can't do much.
          return;
        }

        // The font type is determined on the fly, ignoring anything that may be set in the parent font logic configuration.
        newFontData['type'] = determineFontType(newFontData['font_family'])

        // The selected variants also come straight from the font logic right now.
        if (typeof fonts_logic.font_weights !== 'undefined') {
          newFontData['variants'] = fonts_logic.font_weights
        }

        if (typeof connected_field_data.font_size !== 'undefined' && false !== connected_field_data.font_size) {
          newFontData['font_size'] = connected_field_data.font_size

          // The font weight (selected_variants), letter spacing and text transform all come together from the font styles (intervals).
          // We just need to find the one that best matches the connected field given font size (if given).
          // Please bear in mind that we expect the font logic styles to be preprocessed, without any overlapping and using numerical keys.
          if (typeof fonts_logic.font_styles_intervals !== 'undefined' && _.isArray(fonts_logic.font_styles_intervals) && fonts_logic.font_styles_intervals.length > 0) {
            let idx = 0
            while (idx < fonts_logic.font_styles_intervals.length - 1 &&
              typeof fonts_logic.font_styles_intervals[idx].end !== 'undefined' &&
              fonts_logic.font_styles_intervals[idx].end <= connected_field_data.font_size.value) {

              idx++
            }

            // We will apply what we've got.
            if (!_.isEmpty(fonts_logic.font_styles_intervals[idx].font_weight)) {
              newFontData['selected_variants'] = fonts_logic.font_styles_intervals[idx].font_weight
            }
            if (!_.isEmpty(fonts_logic.font_styles_intervals[idx].letter_spacing)) {
              newFontData['letter_spacing'] = fonts_logic.font_styles_intervals[idx].letter_spacing
            }
            if (!_.isEmpty(fonts_logic.font_styles_intervals[idx].text_transform)) {
              newFontData['text_transform'] = fonts_logic.font_styles_intervals[idx].text_transform
            }
          }

          // The line height is determined by getting the value of the polynomial function determined by points.
          if (typeof fonts_logic.font_size_to_line_height_points !== 'undefined' && _.isArray(fonts_logic.font_size_to_line_height_points)) {
            let result = regression.logarithmic(fonts_logic.font_size_to_line_height_points, {precision: 2})
            let fontsize = connected_field_data.font_size.value
            let lineHeight = result.predict(fontsize)[1]
            newFontData['line_height'] = {value: lineHeight}
          }
        }

        serializedNewFontData = CustomifyFontSelectFields.encodeValues(newFontData)
        setting.set(serializedNewFontData)
      })
    }
  }

  const determineFontType = function(fontFamily) {
    // The default is Google.
    let fontType = 'google'

    // We will follow a stack in the following order: theme fonts, cloud fonts, standard fonts, Google fonts.
    if (typeof customify_settings.theme_fonts[fontFamily] !== 'undefined') {
      fontType = 'theme_font'
    } else if (typeof customify_settings.cloud_fonts[fontFamily] !== 'undefined') {
      fontType = 'cloud_font'
    } else if (typeof customify_settings.std_fonts[fontFamily] !== 'undefined') {
      fontType = 'std_font'
    }

    return fontType
  }

  const bindConnectedFields = function () {
    _.each(masterSettingIds, function (parent_setting_id) {
      if (typeof apiSettings[parent_setting_id] !== 'undefined') {
        let parent_setting_data = apiSettings[parent_setting_id]
        let parent_setting = api(parent_setting_id)

        if (typeof parent_setting_data.connected_fields !== 'undefined') {
          fontsConnectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback(parent_setting_data, parent_setting_id)
          parent_setting.bind(fontsConnectedFieldsCallbacks[parent_setting_id])
        }
      }
    })
  }

  const unbindConnectedFields = function () {
    _.each(masterSettingIds, function (parent_setting_id) {
      if (typeof apiSettings[parent_setting_id] !== 'undefined') {
        let parent_setting_data = apiSettings[parent_setting_id]
        let parent_setting = api(parent_setting_id)

        if (typeof parent_setting_data.connected_fields !== 'undefined' && typeof fontsConnectedFieldsCallbacks[parent_setting_id] !== 'undefined') {
          parent_setting.unbind(fontsConnectedFieldsCallbacks[parent_setting_id])
        }
        delete fontsConnectedFieldsCallbacks[parent_setting_id]
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
    let data = $(this).data('fonts_logic')

    if (!_.isUndefined(data)) {
      $.each(data, function (setting_id, config) {
        set_field_fonts_logic_config(setting_id, config)
      })
    }

    // In case this palette has values (options) attached to it, let it happen.
    $(this).trigger('customify:preset-change')
  }

  const set_field_fonts_logic_config = function (setting_id, config) {
    apiSettings[setting_id].fonts_logic = config

    // We also need to trigger a fake setting value change since the master font controls don't usually hold a (usable) value.
    let setting = api(setting_id)
    if (_.isUndefined(setting)) {
      return
    }

    // We will set the entire config as the master font field value just because it ensures us that,
    // when new info arrives, the setting callbacks will be fired (.set() doesn't do anything if the new value is the same as the old).
    // Also some entries will be used to set the master font subfields (mainly font family).
    // This value is not used in any other way!
    let serializedNewFontData = CustomifyFontSelectFields.encodeValues(config)
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

  return {
    masterSettingIds: masterSettingIds
  }

})(jQuery, window, wp)
