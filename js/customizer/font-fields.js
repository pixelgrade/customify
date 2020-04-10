/** @namespace customify */
window.customify = window.customify || {};

// This is for the Customizer Font control
(function ($, customify, wp) {

  /**
   * Expose the API publicly on window.customify.fontFields
   *
   * @namespace customify.fontFields
   */
  customify.fontFields = function () {
    const
      wrapperSelector = '.font-options__wrapper',
      valueHolderSelector = '.customify_font_values',
      fontFamilySelector = '.customify_font_family',
      fontWeightSelector = '.customify_font_weight',
      fontSubsetsSelector = '.customify_font_subsets',
      selectPlaceholder = 'Select a font family',
      weightPlaceholder = 'Select a font weight',
      subsetPlaceholder = 'Extra Subsets'

    const api = wp.customize

    // We will use this to remember that we are self-updating the field from the subfields.
    // We will save this info for each setting ID.
    const updatingValue = {},
      loadingValue = {}

    const init = function () {
      let $fontFamilyFields = $(fontFamilySelector)

      // Add the Google Fonts opts to each control.
      if (typeof api.settings['google_fonts_opts'] !== 'undefined') {
        $fontFamilyFields.each(function (i, el) {
          let google_opts_placeholder = $(el).find('.google-fonts-opts-placeholder').first()
          if (google_opts_placeholder) {
            // Replace the placeholder with the HTML for the Google fonts select options.
            google_opts_placeholder.replaceWith(api.settings['google_fonts_opts'])

            // The active font family might be a Google font so we need to set the current value after we've added the options.
            let active_font_family = $(el).data('active_font_family')
            if (typeof active_font_family !== 'undefined') {
              $(el).val(active_font_family)
            }
          }
        })
      }

      // Initialize the select2 field for the font family
      $fontFamilyFields.select2({
        placeholder: selectPlaceholder
      })

      // We only need to bing to the original select since select2 triggers the event for this one when changed.
      $fontFamilyFields.on('change', function (e) {
        let new_option = $(e.target).find('option:selected'),
          wrapper = $(e.target).closest(wrapperSelector)

        // Update the weight subfield with the new options given by the selected font family.
        updateWeightField(new_option, wrapper)

        // Update the subset subfield with the new options given by the selected font family.
        updateSubsetField(new_option, wrapper)

        // Serialize subfield values and refresh the fonts in the preview window.
        selfUpdateValue(wrapper)
      }).on('input', function (e) {
        // Mark this input as touched by the user.
        $(e.target).data('touched', true)
      })

      // Handle the reverse value direction, when the customize setting is updated and the subfields need to update their values.
      $fontFamilyFields.each(function (i, el) {
        let wrapper = $(el).closest(wrapperSelector),
          value_holder = wrapper.children(valueHolderSelector),
          setting_id = $(value_holder).data('customize-setting-link'),
          setting = api(setting_id)

        setting.bind(function (newValue, oldValue) {
          if (!updatingValue[this.id]) {
            value_holder.val(newValue)

            loadFontValue(wrapper)
          }
        })
      })

      // Initialize the select2 field for the font weight
      $(fontWeightSelector).each(function (i, el) {

        let select2_args = {
          theme: 'classic',
          placeholder: weightPlaceholder,
          minimumResultsForSearch: 10,
        }

        // all this fuss is for the case when the font doesn't come with variants from PHP, like a theme_font
        if (this.options.length === 0) {
          let wrapper = $(el).closest(wrapperSelector),
            font = wrapper.find(fontFamilySelector),
            option = font[0].options[font[0].selectedIndex],
            variants = maybeJsonParse($(option).data('variants')),
            data = [],
            selected_variants = $(el).data('default') || null

          if (typeof variants === 'undefined') {
            $(this).hide();
            $(this).prev('label').hide();
            return
          }

          $.each(variants, function (index, weight) {
            let thisValue = {
              id: weight,
              text: weight
            }

            // @todo We actually do not support multiple selected variants. Maybe we should? Right now we don't use multiple selections.
            // No we should not support multiple variants. Web Font Loader loads whatever weights are in use.
            if (selected_variants !== null && weight == selected_variants) {
              thisValue.selected = true
            }

            data.push(thisValue)
          })

          if (data !== []) {
            select2_args.data = data
          }
        }

        // Mark this input as not touched by the user.
        $(this).data('touched', false)

        $(this).select2(
          select2_args
        )

        $(this).on('change', function (e) {
          let wrapper = $(e.target).closest(wrapperSelector)

          // Serialize subfield values and refresh the fonts in the preview window.
          selfUpdateValue(wrapper)
        }).on('input', function (e) {
          // Mark this input as touched by the user.
          $(e.target).data('touched', true)
        })
      })

      // Initialize the select2 field for the font subsets
      // Mark this input as not touched by the user.
      $(fontSubsetsSelector).data('touched', false)

      $(fontSubsetsSelector).select2({
        placeholder: subsetPlaceholder,
        theme: 'classic',
        minimumResultsForSearch: 10,
      })

      $(fontSubsetsSelector).on('change', function (e) {
        let wrapper = $(e.target).closest(wrapperSelector)

        // Serialize subfield values and refresh the fonts in the preview window.
        selfUpdateValue(wrapper)
      }).on('input', function (e) {
        // Mark this input as touched by the user.
        $(e.target).data('touched', true)
      })

      let rangers = $fontFamilyFields.parents(wrapperSelector).find('input[type=range]'),
        selects = $fontFamilyFields.parents(wrapperSelector).find('select').not('select[class*=\' select2\'],select[class^=\'select2\']')

      // Initialize the all the regular selects in the font controls
      if (selects.length > 0) {
        // Mark these inputs as not touched by the user.
        $(selects).data('touched', false)

        selects.on('change', function (e) {
          let wrapper = $(e.target).closest(wrapperSelector)

          // Serialize subfield values and refresh the fonts in the preview window.
          selfUpdateValue(wrapper)
        }).on('input', function (e) {
          // Mark this input as touched by the user.
          $(e.target).data('touched', true)
        })
      }

      // Initialize the all the range fields in the font controls
      if (rangers.length > 0) {
        // Mark these inputs as not touched by the user.
        $(rangers).data('touched', false)

        rangers.on('change', function (e) {
          let wrapper = $(e.target).closest(wrapperSelector)

          // Serialize subfield values and refresh the fonts in the preview window.
          selfUpdateValue(wrapper)

          api.previewer.send('font-changed')
        }).on('input', function (e) {
          // Mark this input as touched by the user.
          $(e.target).data('touched', true)
        })
      }

      // When the previewer window is ready, render the fonts
      const self = this
      api.previewer.bind('ready', function () {
        self.renderFonts()
      })
    }

    /**
     * This function updates the data in font weight selector from the given <option> element
     *
     * @param option
     * @param wrapper
     */
    function updateWeightField (option, wrapper) {
      let variants = maybeJsonParse($(option).data('variants')),
        $font_weights = wrapper.find(fontWeightSelector),
        selectedVariant = $font_weights.val() ? $font_weights.val() : $font_weights.data('default'),
        newVariants = [],
        id = wrapper.find(valueHolderSelector).data('customizeSettingLink')

      // We need to clear the old select2 field and reinitialize it.
      $($font_weights).select2().empty()

      // Mark this input as not touched by the user.
      $($font_weights).data('touched', false)

      if (typeof variants === 'undefined' || Object.keys(variants).length < 2 || !_.isUndefined($font_weights.data('disabled'))) {
        $font_weights.parent().hide();
        $font_weights.parent().prev('label').hide();
        return
      }

      // we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
      $.each(variants, function (index, variant) {
        let newVariant = {
          'id': variant,
          'text': variant
        }

        // Leave the comparison loose.
        if (selectedVariant == variant) {
          newVariant.selected = true
        }

        newVariants.push(newVariant)
      })

      // Reinitialize select2 with new variants.
      $($font_weights).select2({
        theme: 'classic',
        data: newVariants,
        minimumResultsForSearch: 10,
      })

      $($font_weights).on('change', function (e) {
        let wrapper = $(e.target).closest(wrapperSelector)

        // Mark this input as touched by the user.
        $(e.target).data('touched', true)

        // Serialize subfield values and refresh the fonts in the preview window.
        selfUpdateValue(wrapper)
      })

      $font_weights.parent().show()
    }

    /**
     *  This function updates the data in font subset selector from the given <option> element
     * @param option
     * @param wrapper
     */
    function updateSubsetField (option, wrapper) {
      let subsets = maybeJsonParse($(option).data('subsets')),
        font_subsets = wrapper.find(fontSubsetsSelector),
        newSubsets = []

      // We need to clear the old select2 field and reinitialize it.
      $(font_subsets).select2().empty()

      // Mark this input as not touched by the user.
      $(font_subsets).data('touched', false)

      if (typeof subsets === 'undefined' || Object.keys(subsets).length < 2 || !_.isUndefined(font_subsets.data('disabled'))) {
        font_subsets.parent().hide();
        font_subsets.parent().prev('label').hide();
        return
      }

      font_subsets.parent().show();
      font_subsets.parent().prev('label').show();

      // Attempt to keep (some of) the previously selected subsets, depending on what the new font supports.
      let currentFontValue = maybeJsonParse(wrapper.children(valueHolderSelector).val())
      let selectedSubsets = []
      if (!_.isUndefined(currentFontValue.selected_subsets) && !_.isEmpty(currentFontValue.selected_subsets)) {
        selectedSubsets = currentFontValue.selected_subsets
        // Make sure it is an array
        if (!Array.isArray(selectedSubsets)) {
          selectedSubsets = Object.keys(selectedSubsets).map(function (key) {
            return selectedSubsets[key]
          })
        }
      }

      // we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
      $.each(subsets, function (index, subset) {
        // We want to skip the 'latin' subset since that is loaded by default.
        if ('latin' === subset) {
          return
        }

        let newSubset = {
          'id': subset,
          'text': subset
        }

        if (selectedSubsets.indexOf(subset) !== -1) {
          newSubset.selected = true
        }

        newSubsets.push(newSubset)
      })

      $(font_subsets).select2({
        data: newSubsets
      }).on('change', function (e) {
        let wrapper = $(e.target).closest(wrapperSelector)

        // Mark this input as touched by the user.
        $(e.target).data('touched', true)

        // Serialize subfield values and refresh the fonts in the preview window.
        selfUpdateValue(wrapper)
      })
    }

    const getValue = function (wrapper) {
      let value_holder = wrapper.children(valueHolderSelector)

      if (value_holder.length) {
        return maybeJsonParse(value_holder.val())
      }

      return []
    }

    const updateValue = function (wrapper, value) {
      let value_holder = wrapper.children(valueHolderSelector),
        setting_id = $(value_holder).data('customize-setting-link'),
        setting = api(setting_id)

      if (!value_holder.length) {
        return
      }

      if (_.isArrayLikeObject(value)) {
        value = encodeValues(value)
      }

      // Set the serialized value in the hidden field.
      value_holder.val(value)
      // Update also the Customizer setting value.
      setting.set(value)
    }

    /**
     * Serialize the value for our entire font field.
     * It collects values and saves them (encoded) into the `.customify_font_values` input's value
     */
    const selfUpdateValue = function (wrapper) {
      let options_list = $(wrapper).find('.font-options__options-list'),
        inputs = options_list.find('[data-field]'),
        value_holder = wrapper.children(valueHolderSelector),
        oldValue = maybeJsonParse(value_holder.val()),
        setting_id = $(value_holder).data('customize-setting-link'),
        setting = api(setting_id),
        newFontData = _.isEmpty(oldValue) ? {} : oldValue

      // If we are already self-updating this and we haven't finished, we need to stop here to prevent infinite loops
      // This call might have come from a subfield detecting the change the triggering a further update_font_value()
      if (true === updatingValue[setting_id]) {
        return
      }

      // If we are loading this setting value and haven't finished, there is no point in updating it as this would cause infinite loops.
      if (true === loadingValue[setting_id]) {
        return
      }

      // Mark the fact that we are self-updating the field value
      updatingValue[setting_id] = true

      inputs.each(function (key, el) {
        const $el = $(el)
        const field = $el.data('field')
        let value = $el.val()

        // We skip disabled subfields.
        // We only pick up subfields values that have been touched by the user or values that are missing in the oldValue.
        if (_.isUndefined(field) || $el.data('disabled') || (!$el.data('touched') && !_.isUndefined(newFontData[field]))) {
          return
        }

        if ('font_family' === field) {
          const selected_opt = $(el.options[el.selectedIndex]),
            subsets = selected_opt.data('subsets'),
            variants = selected_opt.data('variants'),
            src = selected_opt.data('src')

          if (src) {
            newFontData['src'] = src
          } else {
            delete newFontData['src']
          }

          if (!_.isUndefined(variants)) {
            newFontData['variants'] = maybeJsonParse(variants)
          } else {
            delete newFontData['variants']
          }

          if (!_.isUndefined(subsets)) {
            newFontData['subsets'] = maybeJsonParse(subsets)
          } else {
            delete newFontData['subsets']
          }
        }

        if (!_.isUndefined(value) && !_.isNull(value) && value !== '') {
          if (_.includes(['letter_spacing', 'line_height', 'font_size'], field)) {
            // Standardize the value.
            value = standardizeNumericalValue(value, el, false)
          }

          newFontData[field] = value
        }
      })

      // Serialize the newly gathered font data
      let serializedNewFontData = encodeValues(newFontData)
      // Set the serialized value in the hidden field.
      value_holder.val(serializedNewFontData)
      // Update also the Customizer setting value.
      setting.set(serializedNewFontData)

      // Finished with the field value self-updating.
      updatingValue[setting_id] = false

      return newFontData
    }

    /**
     * This function is a reverse of update_font_value(), initializing the entire font field controls based on the value stored in the hidden input.
     */
    function loadFontValue (wrapper) {
      let options_list = $(wrapper).find('.font-options__options-list'),
        inputs = options_list.find('[data-field]'),
        value_holder = wrapper.children(valueHolderSelector),
        value = maybeJsonParse(value_holder.val()),
        setting_id = $(value_holder).data('customize-setting-link')

      // If we are already loading this setting value and haven't finished, there is no point in starting again.
      if (true === loadingValue[setting_id]) {
        return
      }

      // Mark the fact that we are loading the field value
      loadingValue[setting_id] = true

      inputs.each(function (key, el) {
        const $el = $(el)
        const field = $el.data('field')

        // In the case of select2, only the original selects have the data field, thus excluding select2 created select DOM elements
        if (typeof field === 'undefined' || field === '' || typeof value[field] === 'undefined') {
          return
        }

        // We will do this only for numerical sub-fields.
        if (_.includes(['letter_spacing', 'line_height', 'font_size'], field)) {
          let subfieldValue = standardizeNumericalValue(value[field], el)

          // Make sure that the unit and value_unit attributes are in place.
          if (subfieldValue.unit !== '') {
            $el.data('value_unit', subfieldValue.unit)
            if (_.isEmpty($el.attr('unit'))) {
              $el.attr('unit', subfieldValue.unit)
            }
          }

          // If the field unit and value unit differ, we have some conversion to do.
          // We will convert the received value to the appropriate unit declared by the input.
          // We will use a guessed base size of 16px. Not an exact conversion, but it will have to do.
          const baseSize = 16
          const subfieldUnit = $el.attr('unit').trim().toLowerCase()
          const subfieldValueUnit = $el.data('value_unit').trim().toLowerCase()
          // The comparison is intentionally loose.
          if (subfieldUnit != subfieldValueUnit) {
            if (_.includes(['em', 'rem'], subfieldValueUnit) && 'px' === subfieldUnit) {
              // We will have to multiply the value.
              subfieldValue.value = subfieldValue.value * baseSize
            } else if (_.includes(['em', 'rem'], subfieldUnit) && 'px' === subfieldValueUnit) {
              // We will have to divide the value.
              subfieldValue.value = subfieldValue.value / baseSize
            }
          }

          // If this field has a min/max attribute we need to make sure that those attributes allow for the value we are trying to impose.
          if ($el.attr('min') && $el.attr('min') > subfieldValue.value) {
            $el.attr('min', subfieldValue.value)
          }
          if ($el.attr('max') && $el.attr('max') < subfieldValue.value) {
            $el.attr('max', subfieldValue.value)
          }

          $el.val(subfieldValue.value)
        } else {
          $el.val(value[field])
        }

        // Mark this input as not touched by the user.
        $el.data('touched', false)

        $el.trigger('change')
      })

      // Finished with the field value loading.
      loadingValue[setting_id] = false
    }

    const maybeJsonParse = function (value) {
      let parsed

      //try and parse it, with decodeURIComponent
      try {
        parsed = JSON.parse(decodeURIComponent(value))
      } catch (e) {

        // in case of an error, treat is as a string
        parsed = value
      }

      return parsed
    }

    const encodeValues = function (obj) {
      return encodeURIComponent(JSON.stringify(obj))
    }

    const renderFonts = function () {
      $('.customify_font_family').select2({
        theme: 'classic',
        minimumResultsForSearch: 10,
      }).trigger('change')
    }

    /**
     * Given a value we will standardize it to an array with 'value' and 'unit'.
     *
     * @param value
     * @param input Optional. The input this value was extracted from
     * @param valueFirst Optional. Whether to give higher priority to value related data, or to input related one.
     */
    const standardizeNumericalValue = function (value, input, valueFirst = true) {
      const standardValue = {value: false, unit: ''}

      if (!isNaN(value)) {
        standardValue.value = value
      } else if (typeof value.value !== 'undefined') {
        standardValue.value = value.value
        if (typeof value.unit !== 'undefined') {
          standardValue.unit = value.unit.toLowerCase()
        }
      } else if (typeof value[0] !== 'undefined') {
        standardValue.value = value[0]
        if (typeof value[1] !== 'undefined') {
          standardValue.unit = value[1].toLowerCase()
        }
      } else if (typeof value === 'string') {
        const matches = value.match(/^([\d.\-+]+)(.+)/i)
        if (matches !== null && typeof matches[1] !== 'undefined') {
          standardValue.value = matches[1]
          if (!_.isEmpty(matches[2])) {
            standardValue.unit = matches[2].toLocaleLowerCase()
          }
        }
      }

      if (_.isEmpty(standardValue.unit)) {
        // If we are given an input, we will attempt to extract the unit from its attributes.
        let fallbackInputUnit = ''

        if (valueFirst) {
          if (!_.isEmpty($(input).data('value_unit'))) {
            fallbackInputUnit = $(input).data('value_unit').toLowerCase()
          } else if (!_.isEmpty($(input).data('unit'))) {
            fallbackInputUnit = $(input).data('unit').toLowerCase()
          }
        } else {
          if (!_.isEmpty($(input).data('unit'))) {
            fallbackInputUnit = $(input).data('unit').toLowerCase()
          } else if (!_.isEmpty($(input).data('value_unit'))) {
            fallbackInputUnit = $(input).data('value_unit').toLowerCase()
          }
        }
        standardValue.unit = fallbackInputUnit
      }

      return standardValue
    }

    const determineFontType = function(fontFamily) {
      // The default is Google.
      let fontType = 'google'

      // We will follow a stack in the following order: theme fonts, cloud fonts, standard fonts, Google fonts.
      if (typeof customify.config.theme_fonts[fontFamily] !== 'undefined') {
        fontType = 'theme_font'
      } else if (typeof customify.config.cloud_fonts[fontFamily] !== 'undefined') {
        fontType = 'cloud_font'
      } else if (typeof customify.config.std_fonts[fontFamily] !== 'undefined') {
        fontType = 'std_font'
      }

      return fontType
    }

    /**
     * Will convert an array of CSS like variants into their FVD equivalents. Web Font Loader expects this format.
     * @link https://github.com/typekit/fvd
     */
    function convertFontVariantToFVD (variant) {
      variant = String(variant)

      let fontStyle = 'n' // normal
      if (-1 !== variant.indexOf('italic')) {
        fontStyle = 'i'
        variant = variant.replace('italic', '')
      } else if (-1 !== variant.indexOf('oblique')) {
        fontStyle = 'o'
        variant = variant.replace('oblique', '')
      }

      let fontWeight

//  The equivalence:
//
//			1: 100
//			2: 200
//			3: 300
//			4: 400 (default, also recognized as 'normal')
//			5: 500
//			6: 600
//			7: 700 (also recognized as 'bold')
//			8: 800
//			9: 900

      switch (variant) {
        case '100':
          fontWeight = '1'
          break
        case '200':
          fontWeight = '2'
          break
        case '300':
          fontWeight = '3'
          break
        case '500':
          fontWeight = '5'
          break
        case '600':
          fontWeight = '6'
          break
        case '700':
        case 'bold':
          fontWeight = '7'
          break
        case '800':
          fontWeight = '8'
          break
        case '900':
          fontWeight = '9'
          break
        default:
          fontWeight = '4'
          break
      }

      return fontStyle + fontWeight
    }

    return {
      init: init,
      getValue: getValue,
      updateValue: updateValue,
      selfUpdateValue: selfUpdateValue,
      encodeValues: encodeValues,
      standardizeNumericalValue: standardizeNumericalValue,
      determineFontType: determineFontType,
      convertFontVariantToFVD: convertFontVariantToFVD,
      renderFonts: renderFonts,
    }
  }()
})(jQuery, customify, wp)
