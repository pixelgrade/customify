/** @namespace customify */
window.customify = window.customify || parent.customify || {};

// This is for the Customizer Font control
(function ($, customify, wp) {

  /**
   * Expose the API publicly on window.customify.fontFields
   *
   * @namespace customify.fontFields
   */
  if ( typeof customify.fontFields === 'undefined' ) {
    customify.fontFields = {}
  }
  _.extend( customify.fontFields, function () {
    const wrapperSelector = '.font-options__wrapper',
      valueHolderSelector = '.customify_font_values',
      fontFamilySelector = '.customify_font_family',
      fontVariantSelector = '.customify_font_weight',
      fontSubsetsSelector = '.customify_font_subsets',
      fontHeadTitleSelector = '.font-options__head .font-options__font-title'

    let familyPlaceholderText ,
      variantAutoText, // This is for the empty value.
      subsetPlaceholderText

    const api = wp.customize

    // We will use this to remember that we are self-updating the field from the subfields.
    // We will save this info for each setting ID.
    const updatingValue = {},
      loadingValue = {}

    const init = function () {
      familyPlaceholderText = customify.l10n.fonts.familyPlaceholderText
      variantAutoText = customify.l10n.fonts.variantAutoText
      subsetPlaceholderText = customify.l10n.fonts.subsetPlaceholderText

      const $fontFamilyFields = $(fontFamilySelector)

      // Add the Google Fonts opts to each control.
      if (typeof api.settings['google_fonts_opts'] !== 'undefined') {
        $fontFamilyFields.each(function (i, el) {
          const googleOptionsPlaceholder = $(el).find('.google-fonts-opts-placeholder').first()
          if (googleOptionsPlaceholder) {
            // Replace the placeholder with the HTML for the Google fonts select options.
            googleOptionsPlaceholder.replaceWith(api.settings['google_fonts_opts'])

            // The active font family might be a Google font so we need to set the current value after we've added the options.
            const activeFontFamily = $(el).data('active_font_family')
            if (typeof activeFontFamily !== 'undefined') {
              $(el).val(activeFontFamily)
            }
          }
        })
      }

      // Initialize the select2 field for the font family
      $fontFamilyFields.select2({
        placeholder: familyPlaceholderText
      })

      // We only need to bind to the original select since select2 triggers the event for this one when changed.
      $fontFamilyFields.on('change', function (event, who) {
        const newFontFamily = event.target.value,
          wrapper = $(event.target).closest(wrapperSelector)

        // Get the new font details
        const newFontDetails = getFontDetails(newFontFamily)

        // Update the font field head title (with the new font family name).
        updateFontHeadTitle(newFontDetails, wrapper)

        // Update the variant subfield with the new options given by the selected font family.
        updateVariantField(newFontDetails, wrapper)

        // Update the subset subfield with the new options given by the selected font family.
        updateSubsetField(newFontDetails, wrapper)

        if (typeof who !== 'undefined' && who === 'customify') {
          // The change was triggered programmatically by Customify.
          // No need to self-update the value.
        } else {
          // Mark this input as touched by the user.
          $(event.target).data('touched', true)

          // Serialize subfield values and refresh the fonts in the preview window.
          selfUpdateValue(wrapper)
        }
      })

      // Handle the reverse value direction, when the customize setting is updated and the subfields need to update their values.
      $fontFamilyFields.each(function (i, el) {
        const wrapper = $(el).closest(wrapperSelector),
          valueHolder = wrapper.children(valueHolderSelector),
          settingID = $(valueHolder).data('customize-setting-link'),
          setting = api(settingID)

        setting.bind(function (newValue, oldValue) {
          if (!updatingValue[this.id]) {
            valueHolder.val(newValue)

            loadFontValue(wrapper)
          }
        })
      })

      // Initialize the select2 field for the font variant
      initSubfield($(fontVariantSelector), true)

      // Initialize the select2 field for the font subsets
      initSubfield($(fontSubsetsSelector), true, subsetPlaceholderText)

      // Initialize all the regular selects in the font subfields
      initSubfield($fontFamilyFields.parents(wrapperSelector).find('select').not('select[class*=\' select2\'],select[class^=\'select2\']'), false);

      // Initialize the all the range fields in the font subfields
      initSubfield($fontFamilyFields.parents(wrapperSelector).find('input[type=range]'), false);

      handleFontPopupToggle()
    }

    function initSubfield(subfieldList, select2 = false, select2Placeholder = '') {
      if (!subfieldList.length) {
        return
      }
      // Mark these as not touched by the user.
      $(subfieldList).data('touched', false)

      $(subfieldList).on('input change', function (event, who) {
        if (typeof who !== 'undefined' && who === 'customify') {
          // The change was triggered programmatically by Customify.
          // No need to self-update the value.
        } else {
          // Mark this input as touched by the user.
          $(event.target).data('touched', true)

          const wrapper = $(event.target).closest(wrapperSelector)
          // Serialize subfield values and refresh the fonts in the preview window.
          selfUpdateValue(wrapper)
        }
      })

      // If we've been instructed, initialize a select2.
      if (true === select2) {
        const select2Args = {}

        if (!_.isEmpty(select2Placeholder)) {
          select2Args['placeholder'] = select2Placeholder
        }

        $(subfieldList).select2(select2Args)
      }
    }

    const handleFontPopupToggle = function () {
      const $allFontCheckboxes = $('.js-font-option-toggle')
      // Close a font field popup when opening on another font field.
      $allFontCheckboxes.on('click', function () {
        const $checkbox = $(this)
        if ($checkbox.prop('checked') === true) {
          $allFontCheckboxes.not($checkbox).prop('checked', false)
        }
      })
    }

    /**
     * Update the title of the font field (the field head) with the new font family name.
     *
     * @param newFontDetails
     * @param wrapper
     */
    function updateFontHeadTitle (newFontDetails, wrapper) {
      const fontTitleElement = wrapper.find(fontHeadTitleSelector)

      let fontFamilyDisplay = newFontDetails.family
      if (typeof newFontDetails.family_display !== 'undefined') {
        fontFamilyDisplay = newFontDetails.family_display
      }

      $(fontTitleElement).html(fontFamilyDisplay)
    }

    /**
     * This function updates the data in font weight selector from the given <option> element
     *
     * @param newFontDetails
     * @param wrapper
     */
    function updateVariantField (newFontDetails, wrapper) {
      const variants = typeof newFontDetails.variants !== 'undefined' ? newFontDetails.variants : [],
        fontVariantInput = wrapper.find(fontVariantSelector),
        selectedVariant = fontVariantInput.val() ? fontVariantInput.val() : '',
        newVariants = []

      // We clear everything about this subfield.
      fontVariantInput.val(null).empty()
      if (fontVariantInput.hasClass("select2-hidden-accessible")) {
        fontVariantInput.select2('destroy')
      }

      // Mark this input as not touched by the user.
      fontVariantInput.data('touched', false)

      if (typeof variants === 'undefined' || Object.keys(variants).length < 2) {
        fontVariantInput.parent().hide()
        fontVariantInput.parent().prev('label').hide()
        // Mark this input as disabled.
        fontVariantInput.data('disabled', true)
        return
      }

      // Initialize the options with an empty one.
      newVariants.push({
        'id': '',
        'text': variantAutoText
      })

      // we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
      $.each(variants, function (index, variant) {
        let newVariant = {
          'id': variant, // This is the option value.
          'text': variant
        }

        // Leave the comparison loose.
        if (selectedVariant == variant) {
          newVariant.selected = true
        }

        newVariants.push(newVariant)
      })

      // Only reinitialize the select2.
      // No need to rebind on change or on input since those are still bound to the original HTML element.
      fontVariantInput.select2({
        data: newVariants
      })

      fontVariantInput.parent().show()
      fontVariantInput.parent().prev('label').show()
      // Mark this input as enabled.
      fontVariantInput.data('disabled', false)
    }

    /**
     *  This function updates the data in font subset selector from the given <option> element
     * @param newFontDetails
     * @param wrapper
     */
    function updateSubsetField (newFontDetails, wrapper) {
      const subsets = typeof newFontDetails.subsets !== 'undefined' ? newFontDetails.subsets : [],
        fontSubsetsInput = wrapper.find(fontSubsetsSelector),
        newSubsets = []

      // We clear everything about this subfield.
      fontSubsetsInput.val(null).empty()
      if (fontSubsetsInput.hasClass("select2-hidden-accessible")) {
        fontSubsetsInput.select2('destroy')
      }

      // Mark this input as not touched by the user.
      fontSubsetsInput.data('touched', false)

      if (typeof subsets === 'undefined' || Object.keys(subsets).length < 2) {
        fontSubsetsInput.parent().hide()
        fontSubsetsInput.parent().prev('label').hide()
        // Mark this input as disabled.
        fontSubsetsInput.data('disabled', true)
        return
      }

      // Attempt to keep (some of) the previously selected subsets, depending on what the new font supports.
      const currentFontValue = maybeJsonParse(wrapper.children(valueHolderSelector).val())
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

        const newSubset = {
          'id': subset,
          'text': subset
        }

        if (selectedSubsets.indexOf(subset) !== -1) {
          newSubset.selected = true
        }

        newSubsets.push(newSubset)
      })

      // Only reinitialize the select2.
      // No need to rebind on change or on input since those are still bound to the original HTML element.
      fontSubsetsInput.select2({
        data: newSubsets,
        placeholder: subsetPlaceholderText
      })

      fontSubsetsInput.parent().show()
      fontSubsetsInput.parent().prev('label').show()
      // Mark this input as enabled.
      fontSubsetsInput.data('disabled', false)
    }

    /**
     * Serialize the value for our entire font field.
     * It collects values and saves them (encoded) into the `.customify_font_values` input's value
     */
    const selfUpdateValue = function (wrapper) {
      const valueHolder = wrapper.find(valueHolderSelector).first(),
        settingID = valueHolder.data('customize-setting-link')

      // If we are already self-updating this and we haven't finished, we need to stop here to prevent infinite loops
      // This call might have come from a subfield detecting the change thus triggering a further selfUpdateValue()
      if (true === updatingValue[settingID]) {
        return
      }

      // If we are loading this setting value and haven't finished,
      // there is no point in updating it as this would cause infinite loops.
      if (true === loadingValue[settingID]) {
        return
      }

      // Mark the fact that we are self-updating the field value
      updatingValue[settingID] = true

      const optionsList = wrapper.find('.font-options__options-list'),
        inputs = optionsList.find('[data-field]'),
        oldValue = maybeJsonParse(valueHolder.val()),
        setting = api(settingID),
        newFontData = _.isEmpty(oldValue) ? {} : oldValue

      inputs.each(function (key, input) {
        const $input = $(input)
        const field = $input.data('field')
        let value = $input.val()

        // We only pick up subfields values that have been touched by the user, that are enabled (visible) or values that are missing in the oldValue.
        if (_.isUndefined(field) || $input.data('disabled') || (!$input.data('touched') && !_.isUndefined(newFontData[field]))) {
          return
        }

        if ('font_family' === field) {
          // Get the src of the selected option.
          const src = $(input.options[input.selectedIndex]).data('src')

          if (src) {
            newFontData['src'] = src
          } else {
            newFontData['src'] = undefined
          }
        }

        if (!_.isUndefined(value) && !_.isNull(value) && value !== '') {
          if (_.includes(['letter_spacing', 'line_height', 'font_size'], field)) {
            // Standardize the value.
            value = standardizeNumericalValue(value, input, false)
          }

          newFontData[field] = value
        } else {
          newFontData[field] = undefined
        }
      })

      // We don't need to store font variants or subsets list in the value
      // since we will get those from the global font details.
      newFontData['variants'] = undefined
      newFontData['subsets'] = undefined

      // We need to make sure that we don't "use" any variants or subsets not supported by the new font (values passed over from the old value).
      // Get the new font details
      const newFontDetails = getFontDetails(newFontData['font_family'])
      // Check the font variant
      if (typeof newFontData['font_variant'] !== 'undefined' && typeof newFontDetails.variants !== 'undefined' && Object.keys(newFontDetails.variants).length > 0) {
        if (!_.includes(newFontDetails.variants, newFontData['font_variant'])) {
          // The new font doesn't have this variant. Nor should the value.
         newFontData['font_variant'] = undefined
        }
      } else {
        // The new font has no variants. Nor should the value.
        newFontData['font_variant'] = undefined
      }
      // Check the subsets
      if (typeof newFontData['selected_subsets'] !== 'undefined' && typeof newFontDetails.subsets !== 'undefined' && Object.keys(newFontDetails.subsets).length > 0) {
        // We will use the intersection between the font's subsets and the selected subsets.
        newFontData['selected_subsets'] = _.intersection(newFontData['selected_subsets'],newFontDetails.subsets)
      } else {
        // The new font has no subsets. Nor should the value.
        newFontData['selected_subsets'] = undefined
      }

      // Serialize the newly gathered font data
      const serializedNewFontData = encodeValues(newFontData)
      // Only update if we have something different.
      if (serializedNewFontData !== valueHolder.val()) {
        // Set the serialized value in the hidden field.
        valueHolder.val(serializedNewFontData)
        // Update also the Customizer setting value.
        setting.set(serializedNewFontData)
      }

      // Finished with the field value self-updating.
      updatingValue[settingID] = false
    }

    /**
     * This function is a reverse of selfUpdateValue(), initializing the entire font field controls
     * based on the value stored in the hidden input.
     */
    function loadFontValue (wrapper) {
      const valueHolder = wrapper.find(valueHolderSelector).first(),
        settingID = valueHolder.data('customize-setting-link')

      // If we are already loading this setting value and haven't finished, there is no point in starting again.
      if (true === loadingValue[settingID]) {
        return
      }

      // Mark the fact that we are loading the field value
      loadingValue[settingID] = true

      const optionsList = $(wrapper).find('.font-options__options-list'),
        inputs = optionsList.find('[data-field]'),
        value = maybeJsonParse(valueHolder.val())

      inputs.each(function (key, input) {
        const $input = $(input)
        const field = $input.data('field')

        // In the case of select2, only the original selects have the data field, thus excluding select2 created select DOM elements
        if (typeof field === 'undefined' || field === '' || typeof value[field] === 'undefined') {
          return
        }

        // We will do this only for numerical sub-fields.
        if (_.includes(['letter_spacing', 'line_height', 'font_size'], field)) {
          const subfieldValue = standardizeNumericalValue(value[field], input)

          // Make sure that the unit and value_unit attributes are in place.
          if (subfieldValue.unit !== '') {
            $input.data('value_unit', subfieldValue.unit)
            if (_.isEmpty($input.attr('unit'))) {
              $input.attr('unit', subfieldValue.unit)
            }
          }

          // If the field unit and value unit differ, we have some conversion to do.
          // We will convert the received value to the appropriate unit declared by the input.
          // We will use a guessed base size of 16px. Not an exact conversion, but it will have to do.
          const baseSize = 16
          const subfieldUnit = $input.attr('unit').trim().toLowerCase()
          const subfieldValueUnit = $input.data('value_unit').trim().toLowerCase()
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
          if ($input.attr('min') && $input.attr('min') > subfieldValue.value) {
            $input.attr('min', subfieldValue.value)
          }
          if ($input.attr('max') && $input.attr('max') < subfieldValue.value) {
            $input.attr('max', subfieldValue.value)
          }

          $input.val(subfieldValue.value)
        } else {
          $input.val(value[field])
        }

        // Mark this input as not touched by the user.
        $input.data('touched', false)

        $input.trigger('change', ['customify'])
      })

      // Finished with the field value loading.
      loadingValue[settingID] = false
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

    /**
     * Given a value we will standardize it to an array with 'value' and 'unit'.
     *
     * This is a mirror logic of the server-side one from Customify_Fonts_Global::standardizeNumericalValue()
     *
     * @param value
     * @param input Optional. The input this value was extracted from
     * @param valueFirst Optional. Whether to give higher priority to value related data, or to input related one.
     */
    const standardizeNumericalValue = function (value, input = false, valueFirst = true) {
      const standardValue = {value: false, unit: false}

      if (_.includes(['','false',false], value)) {
        return standardValue
      }

      if (!isNaN(value)) {
        standardValue.value = value
      } else if (typeof value.value !== 'undefined') {
        standardValue.value = value.value
        if (typeof value.unit !== 'undefined') {
          standardValue.unit = value.unit
        }
      } else if (typeof value[0] !== 'undefined') {
        standardValue.value = value[0]
        if (typeof value[1] !== 'undefined') {
          standardValue.unit = value[1]
        }
      } else if (typeof value === 'string') {
        // We will get everything in front that is a valid part of a number (float including).
        const matches = value.match(/^([\d.\-+]+)(.+)/i)
        if (matches !== null && typeof matches[1] !== 'undefined') {
          standardValue.value = matches[1]
          if (!_.isEmpty(matches[2])) {
            standardValue.unit = matches[2]
          }
        } else {
          // If we could not extract anything useful we will trust the developer and leave it like that.
          standardValue.value = value
        }
      }

      if (false !== input && (false === standardValue.unit || _.isEmpty(standardValue.unit))) {
        // If we are given an input, we will attempt to extract the unit from its attributes.
        let fallbackInputUnit = ''
        const $input = $(input)

        if (valueFirst) {
          if (!_.isEmpty($input.data('value_unit'))) {
            fallbackInputUnit = $input.data('value_unit')
          } else if (!_.isEmpty($input.data('unit'))) {
            fallbackInputUnit = $input.data('unit')
          }
        } else {
          if (!_.isEmpty($input.data('unit'))) {
            fallbackInputUnit = $input.data('unit')
          } else if (!_.isEmpty($input.data('value_unit'))) {
            fallbackInputUnit = $input.data('value_unit')
          }
        }
        standardValue.unit = fallbackInputUnit
      }

      return standardValue
    }

    const determineFontType = function (fontFamily) {
      // The default is a standard font (aka no special loading or processing).
      let fontType = 'std_font'

      // We will follow a stack in the following order: theme fonts, cloud fonts, standard fonts, Google fonts.
      if (typeof customify.fonts.theme_fonts[fontFamily] !== 'undefined') {
        fontType = 'theme_font'
      } else if (typeof customify.fonts.cloud_fonts[fontFamily] !== 'undefined') {
        fontType = 'cloud_font'
      } else if (typeof customify.fonts.google_fonts[fontFamily] !== 'undefined') {
        fontType = 'google_font'
      }

      return fontType
    }

    const getFontDetails = function (fontFamily, fontType = false) {
      if (false === fontType) {
        // We will determine the font type based on font family.
        fontType = determineFontType(fontFamily)
      }

      switch (fontType) {
        case 'theme_font':
          return customify.fonts.theme_fonts[fontFamily]
          break
        case 'cloud_font':
          return customify.fonts.cloud_fonts[fontFamily]
          break
        case 'google_font':
          return customify.fonts.google_fonts[fontFamily]
          break
        case 'std_font':
          if (typeof customify.fonts.std_fonts[fontFamily] !== 'undefined') {
            return customify.fonts.std_fonts[fontFamily]
          }
          break
        default:
      }

      return false
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
      selfUpdateValue: selfUpdateValue,
      encodeValues: encodeValues,
      standardizeNumericalValue: standardizeNumericalValue,
      determineFontType: determineFontType,
      getFontDetails: getFontDetails,
      convertFontVariantToFVD: convertFontVariantToFVD,
    }
  }() )
})(jQuery, customify, wp)
