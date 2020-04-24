;(function ($, window, document) {

  /* global customify.config */
  /* global WebFont */

  $(window).on('load', function () {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript()
  })

  const fonts_cache = []

  // Do everything at document.ready
  $(function () {
    const api = parent.wp.customize
    const customify = parent.customify
    const apiSettings = api.settings.settings
    const regexForMultipleReplace = new RegExp('-', 'g')

    $.each(customify.config.settings, function (key, settingConfig) {
      const propertiesPrefix = typeof settingConfig.properties_prefix === 'undefined' ? '' : settingConfig.properties_prefix
      if (settingConfig.type === 'font') {
        api(key, function (setting) {
          setting.bind(function (newValue) {
            const rawValues = maybeJsonParse(newValue)

            if (typeof rawValues === 'undefined') {
              return
            }

            if (typeof rawValues.font_family !== 'undefined') {
              maybeLoadFontFamily(rawValues)
            }

            const $styleElement = $('#customify_font_output_for_' + settingConfig.html_safe_option_id)
            if (!$styleElement.length) {
              return
            }

            const cssValues = getFontFieldCSSValues(this.id, rawValues)
            if (_.isEmpty(cssValues)) {
              // Empty the style element.
              $styleElement.html('')
              return
            }

            $styleElement.html(getFontFieldCSSCode(this.id, cssValues, propertiesPrefix))
          })
        })

      } else if (typeof apiSettings !== 'undefined'
        && typeof apiSettings[key] !== 'undefined'
        && typeof settingConfig.css !== 'undefined'
        && typeof settingConfig.live !== 'undefined'
        && settingConfig.live === true) {

        api(key, function (setting) {
          setting.bind(function (newValue) {

            $.each(settingConfig.css, function (idx, propertyConfig) {
              // Replace all dashes with underscores thus making the CSS property safe to us in a HTML ID.
              const $styleElement = $('.dynamic_setting_' + settingConfig.html_safe_option_id + '_property_' + propertyConfig.property.replace(regexForMultipleReplace, '_') + '_' + idx)
              if (!$styleElement.length) {
                return
              }

              const properties = {}
              if (typeof propertyConfig.property !== 'undefined' && typeof propertyConfig.selector !== 'undefined') {
                properties[propertyConfig.property] = propertyConfig.selector
              }
              if (typeof propertyConfig.callback_filter !== 'undefined') {
                properties['callback'] = propertyConfig.callback_filter
              }
              if (_.isEmpty(properties)) {
                return
              }

              const cssUpdateArgs = {
                properties: properties,
                propertyValue: newValue,
                negative_value: propertyConfig.hasOwnProperty('negative_value') ? propertyConfig['negative_value'] : false
              }

              if (typeof this.unit !== 'undefined') {
                cssUpdateArgs.unit = this.unit
              }

              $styleElement.cssUpdate(cssUpdateArgs)
            })

          })
        })
      } else if (typeof settingConfig.live === 'object' && settingConfig.live.length > 0) {
        // If the live parameter is an object it means that this is a list of css classes.
        // These classes should be affected by the change of the text fields.
        const fieldClass = settingConfig.live.join()

        // if this field is allowed to modify text then we'll edit this live
        if ($.inArray(settingConfig.type, ['text', 'textarea', 'ace_editor']) > -1) {
          api(key, function (value) {
            value.bind(function (text) {
              let sanitizer = document.createElement('div')

              sanitizer.innerHTML = text
              $(fieldClass).html(text)
            })
          })
        }
      }
    })

    /**
     * HELPERS
     **/

    const getFontFieldCSSValues = function (ID, values) {

      const store = {}

      if (typeof values.font_family !== 'undefined' && !_.includes(['','false',false], values.font_family)) {
        store['font-family'] = values.font_family
      }

      if (typeof values.font_variant !== 'undefined' && !_.includes(['','false',false], values.font_variant)) {
        let variant = values.font_variant

        if (_.isString(variant)) {
          // We may have a style in the variant; attempt to split.
          if (variant.indexOf('italic') !== -1) {
            store['font-style'] = 'italic'
            variant = variant.replace('italic', '')
          } else if (variant.indexOf('oblique') !== -1) {
            store['font-style'] = 'oblique'
            variant = variant.replace('oblique', '')
          }

          // If anything remained, then we have a font weight also.
          if (variant !== '') {
            if (variant === 'regular') {
              variant = 'normal'
            }

            store['font-weight'] = variant
          }
        } else if (_.isNumeric(variant)) {
          store['font-weight'] = variant
        }
      }

      if (typeof values.font_size !== 'undefined' && !_.includes(['','false',false], values.font_size)) {
        let fontSizeUnit = false

        store['font-size'] = values.font_size
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.font_size)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.font_size.value !== 'undefined') {
            store['font-size'] = values.font_size.value
            if (typeof values.font_size.unit !== 'undefined') {
              fontSizeUnit = values.font_size.unit
            }
          } else {
            fontSizeUnit = getFieldUnit(ID, 'font-size')
          }
        } else {
          fontSizeUnit = getFieldUnit(ID, 'font-size')
        }

        if (false !== fontSizeUnit) {
          store['font-size'] += fontSizeUnit
        }
      }

      if (typeof values.letter_spacing !== 'undefined' && !_.includes(['','false',false], values.letter_spacing)) {
        let letterSpacingUnit = false

        store['letter-spacing'] = values.letter_spacing
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.letter_spacing)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.letter_spacing.value !== 'undefined') {
            store['letter-spacing'] = values.letter_spacing.value
            if (typeof values.letter_spacing.unit !== 'undefined') {
              letterSpacingUnit = values.letter_spacing.unit
            }
          } else {
            letterSpacingUnit = getFieldUnit(ID, 'letter-spacing')
          }
        } else {
          letterSpacingUnit = getFieldUnit(ID, 'letter-spacing')
        }

        if (false !== letterSpacingUnit) {
          store['letter-spacing'] += letterSpacingUnit
        }
      }

      if (typeof values.line_height !== 'undefined' && !_.includes(['','false',false], values.line_height)) {
        let lineHeightUnit = false

        store['line-height'] = values.line_height
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.line_height)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.line_height.value !== 'undefined') {
            store['line-height'] = values.line_height.value
            if (typeof values.line_height.unit !== 'undefined') {
              lineHeightUnit = values.line_height.unit
            }
          } else {
            lineHeightUnit = getFieldUnit(ID, 'line-height')
          }
        } else {
          lineHeightUnit = getFieldUnit(ID, 'line-height')
        }

        if (false !== lineHeightUnit) {
          store['line-height'] += lineHeightUnit
        }
      }

      if (typeof values.text_align !== 'undefined' && !_.includes(['','false',false], values.text_align)) {
        store['text-align'] = values.text_align
      }

      if (typeof values.text_transform !== 'undefined' && !_.includes(['','false',false], values.text_transform)) {
        store['text-transform'] = values.text_transform
      }
      if (typeof values.text_decoration !== 'undefined' && !_.includes(['','false',false], values.text_decoration)) {
        store['text-decoration'] = values.text_decoration
      }

      return store
    }

    // Mirror logic of server-side Customify_Fonts_Global::get_font_style()
    const getFontFieldCSSCode = function (ID, values, prefix) {
      const field = customify.config.settings[ID]
      let output = ''

      if (typeof window !== 'undefined' && typeof field.callback !== 'undefined' && typeof window[field.callback] === 'function') {
        return window[field.callback](values, field)
      }

      if (typeof field.selector === 'undefined' || _.isEmpty(field.selector) || _.isEmpty(values)) {
        return output
      }

      // The general CSS allowed properties.
      const subFieldsCSSAllowedProperties = extractAllowedCSSPropertiesFromFontFields(field['fields'])

      // The selector is standardized to a list of simple string selectors, or a list of complex selectors with details.
      // In either case, the actual selector is in the key, and the value is an array (possibly empty).

      // Since we might have simple CSS selectors and complex ones (with special details),
      // for cleanliness we will group the simple ones under a single CSS rule,
      // and output individual CSS rules for complex ones.
      // Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
      const simpleCSSSelectors = []
      const complexCSSSelectors = {}

      _.each(field.selector, function (details, selector) {
        if (_.isEmpty(details.properties)) {
          // This is a simple selector.
          simpleCSSSelectors.push(selector)
        } else {
          complexCSSSelectors[selector] = details
        }
      })

      if (!_.isEmpty(simpleCSSSelectors)) {
        output += '\n' + simpleCSSSelectors.join(', ') + ' {\n'
        output += getFontFieldCSSProperties(values, subFieldsCSSAllowedProperties, prefix)
        output += '}\n'
      }

      if (!_.isEmpty(complexCSSSelectors)) {
        _.each(complexCSSSelectors, function (details, selector) {
          output += '\n' + selector + ' {\n'
          output += getFontFieldCSSProperties(values, details.properties, prefix)
          output += '}\n'
        })
      }

      return output
    }

    const getFontFieldCSSProperties = function (values, allowedProperties = false, prefix = '') {
      let output = ''

      $.each(values, function (property, value) {
        // We don't want to output empty CSS rules.
        if ('' === value || false === value) {
          return
        }

        // If the property is not allowed, skip it.
        if (!isCSSPropertyAllowed(property, allowedProperties)) {
          return
        }

        output += prefix + property + ': ' + value + ';\n'
      })

      return output
    }

    // Mirror logic of server-side Customify_Fonts_Global::isCSSPropertyAllowed()
    const isCSSPropertyAllowed = function (property, allowedProperties = false) {
      // Empty properties are not allowed.
      if (_.isEmpty(property)) {
        return false
      }

      // Everything is allowed if nothing is specified.
      if (_.isEmpty(allowedProperties)) {
        return true
      }

      // For arrays
      if (_.includes(allowedProperties, property)) {
        return true
      }

      // For objects
      if (_.has(allowedProperties, property) && allowedProperties[property]) {
        return true
      }

      return false
    }

    const extractAllowedCSSPropertiesFromFontFields = function (subfields) {
      // Nothing is allowed by default.
      const allowedProperties = {
        'font-family': false,
        'font-weight': false,
        'font-style': false,
        'font-size': false,
        'line-height': false,
        'letter-spacing': false,
        'text-align': false,
        'text-transform': false,
        'text-decoration': false,
      }

      if (_.isEmpty(subfields)) {
        return allowedProperties
      }

      const regexForMultipleReplace = new RegExp('_', 'g')

      // Convert all subfield keys to use dashes not underscores.
      _.each(subfields, function (value, key) {
        const newKey = key.replace(regexForMultipleReplace, '-')
        if (newKey !== key) {
          subfields[newKey] = value
          delete subfields[key]
        }
      })

      // We will match the subfield keys with the CSS properties, but only those that properties that are above.
      // Maybe at some point some more complex matching would be needed here.
      _.each(subfields, function (value, key) {
        if (typeof allowedProperties[key] !== 'undefined') {
          // Convert values to boolean.
          allowedProperties[key] = !!value

          // For font-weight we want font-style to go the same way,
          // since these two are generated from the same subfield: font-weight (actually holding the font variant value).
          if ('font-weight' === key) {
            allowedProperties['font-style'] = allowedProperties[key]
          }
        }
      })

      return allowedProperties
    }

    // This is a mirror logic of the server-side Customify_Fonts_Global::getSubFieldUnit()
    const getFieldUnit = function (ID, field) {
      if (typeof customify.config.settings[ID] === 'undefined' || typeof customify.config.settings[ID].fields[field] === 'undefined') {
        // These fields don't have an unit, by default.
        if (_.includes(['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'], field)) {
          return false
        }

        // The rest of the subfields have pixels as default units.
        return 'px'
      }

      if (typeof customify.config.settings[ID].fields[field].unit !== 'undefined') {
        // Make sure that we convert all falsy unit values to the boolean false.
        return _.includes(['', 'false'], customify.config.settings[ID].fields[field].unit) ? false : customify.config.settings[ID].fields[field].unit
      }

      if (typeof customify.config.settings[ID].fields[field][3] !== 'undefined') {
        // Make sure that we convert all falsy unit values to the boolean false.
        return _.includes(['', 'false'], customify.config.settings[ID].fields[field][3]) ? false : customify.config.settings[ID].fields[field][3]
      }

      return 'px'
    }

    const maybeLoadFontFamily = function (font) {
      if (typeof font.font_family === 'undefined') {
        return
      }

      let family = font.font_family
      // The font family may be a comma separated list like "Roboto, sans"

      const fontType = customify.fontFields.determineFontType(family)
      if ('std_font' === fontType) {
        // Nothing to do for standard fonts
        return
      }

      const fontDetails = customify.fontFields.getFontDetails(family, fontType)

      // Handle theme defined fonts and cloud fonts together since they are very similar.
      if (fontType === 'theme_font' || fontType === 'cloud_font') {

        // Bail if we have no src.
        if (typeof fontDetails.src === undefined) {
          return
        }

        // Handle the font variants
        // First if there is a selected font variant, otherwise all the available variants.
        let variants = typeof font.font_variant !== 'undefined' ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : []
        if (!_.isEmpty(variants)) {
          variants = standardizeToArray(maybeJsonParse(variants))

          if (!_.isEmpty(variants)) {
            family = family + ':' + variants.map(function (variant) {
              return customify.fontFields.convertFontVariantToFVD(variant)
            }).join(',')
          }
        }

        if (fonts_cache.indexOf(family) === -1) {
          WebFont.load({
            custom: {
              families: [family],
              urls: [fontDetails.src]
            },
            classes: false,
            events: false,
            error: function (e) {
              console.log(e)
            },
            active: function () {
              sessionStorage.fonts = true
            }
          })

          // Remember we've loaded this family (with it's variants) so we don't load it again.
          fonts_cache.push(family)
        }
      }
      // Handle Google fonts since Web Font Loader has a special module for them.
      else if (fontType === 'google_font') {

        // Handle the font variants
        // First if there is a selected font variant, otherwise all the available variants.
        let variants = typeof font.font_variant !== 'undefined' ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : []
        if (!_.isEmpty(variants)) {
          variants = standardizeToArray(maybeJsonParse(variants))

          if (!_.isEmpty(variants)) {
            family = family + ':' + variants.join(',')
          }
        }

        let subsets = typeof font.selected_subsets !== 'undefined' ? font.selected_subsets : []
        if (!_.isEmpty(subsets)) {
          subsets = standardizeToArray(maybeJsonParse(subsets))

          if (!_.isEmpty(subsets)) {
            family = family + ':' + subsets.join(',')
          }
        }

        if (fonts_cache.indexOf(family) === -1) {
          WebFont.load({
            google: {families: [family]},
            classes: false,
            events: false,
            error: function (e) {
              console.log(e)
            },
            active: function () {
              sessionStorage.fonts = true
            }
          })

          // Remember we've loaded this family (with it's variants and subsets) so we don't load it again.
          fonts_cache.push(family)
        }

      } else {
        // Maybe Typekit, Fonts.com or Fontdeck fonts
      }
    }

    const standardizeToArray = function (value) {
      if (typeof value === 'string' || typeof value === 'number') {
        value = [value]
      } else if (typeof value === 'object') {
        value = Object.values(value)
      }

      return value
    }

    const maybeJsonParse = function (value) {
      if (typeof value !== 'string') {
        return value
      }

      let parsed

      // Try and parse it, with decodeURIComponent.
      try {
        parsed = JSON.parse(decodeURIComponent(value))
      } catch (e) {

        // in case of an error, treat is as a string
        parsed = value
      }

      return parsed
    }
  })

  function maybeLoadWebfontloaderScript () {
    if (typeof WebFont === 'undefined') {
      let tk = document.createElement('script')
      tk.src = parent.customify.config.webfontloader_url
      tk.type = 'text/javascript'
      let s = document.getElementsByTagName('script')[0]
      s.parentNode.insertBefore(tk, s)
    }
  }
})(jQuery, window, document)
