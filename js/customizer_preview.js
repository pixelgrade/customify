;(function ($, window, document) {

  /* global customify.config */
  /* global WebFont */

  $(window).on('load', function () {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript()
  })

  const maybeLoadWebfontloaderScript = function () {
    if (typeof WebFont === 'undefined') {
      let tk = document.createElement('script')
      tk.src = parent.customify.config.webfontloader_url
      tk.type = 'text/javascript'
      let s = document.getElementsByTagName('script')[0]
      s.parentNode.insertBefore(tk, s)
    }
  }

  const fontsCache = []

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
            if (typeof newValue === 'undefined') {
              return
            }

            if (typeof newValue.font_family !== 'undefined') {
              maybeLoadFontFamily(newValue, this.id)
            }

            const $styleElement = $('#customify_font_output_for_' + settingConfig.html_safe_option_id)
            if (!$styleElement.length) {
              return
            }

            const cssValue = getFontFieldCSSValue(this.id, newValue)
            if (_.isEmpty(cssValue)) {
              // Empty the style element.
              $styleElement.html('')
              return
            }

            $styleElement.html(getFontFieldCSSCode(this.id, cssValue, propertiesPrefix, newValue))
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

    // Mirror logic of server-side Customify_Fonts_Global::getCSSValue()
    const getFontFieldCSSValue = function (settingID, value) {

      const CSSValue = {}

      if (typeof value.font_family !== 'undefined' && !_.includes(['','false',false], value.font_family)) {
        CSSValue['font-family'] = value.font_family
        // "Expand" the font family by appending the fallback stack, if any is available.
        // But only do this, if the value is not already a font stack!
        if (CSSValue['font-family'].indexOf(',') === -1) {
          const fallbackStack = getFontFamilyFallbackStack(CSSValue['font-family'])
          if (fallbackStack.length) {
            CSSValue['font-family'] += ',' + fallbackStack
          }
        }

        CSSValue['font-family'] = sanitizeFontFamilyCSSValue(CSSValue['font-family'])
      }

      if (typeof value.font_variant !== 'undefined' && !_.includes(['','false',false], value.font_variant)) {
        let variant = value.font_variant

        if (_.isString(variant)) {
          // We may have a style in the variant; attempt to split.
          if (variant.indexOf('italic') !== -1) {
            CSSValue['font-style'] = 'italic'
            variant = variant.replace('italic', '')
          } else if (variant.indexOf('oblique') !== -1) {
            CSSValue['font-style'] = 'oblique'
            variant = variant.replace('oblique', '')
          }

          // If anything remained, then we have a font weight also.
          if (variant !== '') {
            if (variant === 'regular' || variant === 'normal') {
              variant = '400'
            }

            CSSValue['font-weight'] = variant
          }
        } else if (_.isNumeric(variant)) {
          CSSValue['font-weight'] = String(variant);
        }
      }

      if (typeof value.font_size !== 'undefined' && !_.includes(['','false',false], value.font_size)) {
        let fontSizeUnit = false

        CSSValue['font-size'] = value.font_size
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(value.font_size)) {
          // If we have a standardized value field (as array), use that.
          if (typeof value.font_size.value !== 'undefined') {
            CSSValue['font-size'] = value.font_size.value
            if (typeof value.font_size.unit !== 'undefined') {
              fontSizeUnit = value.font_size.unit
            }
          } else {
            fontSizeUnit = getFieldUnit(settingID, 'font-size')
          }
        } else {
          fontSizeUnit = getFieldUnit(settingID, 'font-size')
        }

        if (false !== fontSizeUnit) {
          CSSValue['font-size'] += fontSizeUnit
        }
      }

      if (typeof value.letter_spacing !== 'undefined' && !_.includes(['','false',false], value.letter_spacing)) {
        let letterSpacingUnit = false

        CSSValue['letter-spacing'] = value.letter_spacing
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(value.letter_spacing)) {
          // If we have a standardized value field (as array), use that.
          if (typeof value.letter_spacing.value !== 'undefined') {
            CSSValue['letter-spacing'] = value.letter_spacing.value
            if (typeof value.letter_spacing.unit !== 'undefined') {
              letterSpacingUnit = value.letter_spacing.unit
            }
          } else {
            letterSpacingUnit = getFieldUnit(settingID, 'letter-spacing')
          }
        } else {
          letterSpacingUnit = getFieldUnit(settingID, 'letter-spacing')
        }

        if (false !== letterSpacingUnit) {
          CSSValue['letter-spacing'] += letterSpacingUnit
        }
      }

      if (typeof value.line_height !== 'undefined' && !_.includes(['','false',false], value.line_height)) {
        let lineHeightUnit = false

        CSSValue['line-height'] = value.line_height
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(value.line_height)) {
          // If we have a standardized value field (as array), use that.
          if (typeof value.line_height.value !== 'undefined') {
            CSSValue['line-height'] = value.line_height.value
            if (typeof value.line_height.unit !== 'undefined') {
              lineHeightUnit = value.line_height.unit
            }
          } else {
            lineHeightUnit = getFieldUnit(settingID, 'line-height')
          }
        } else {
          lineHeightUnit = getFieldUnit(settingID, 'line-height')
        }

        if (false !== lineHeightUnit) {
          CSSValue['line-height'] += lineHeightUnit
        }
      }

      if (typeof value.text_align !== 'undefined' && !_.includes(['','false',false], value.text_align)) {
        CSSValue['text-align'] = value.text_align
      }

      if (typeof value.text_transform !== 'undefined' && !_.includes(['','false',false], value.text_transform)) {
        CSSValue['text-transform'] = value.text_transform
      }

      if (typeof value.text_decoration !== 'undefined' && !_.includes(['','false',false], value.text_decoration)) {
        CSSValue['text-decoration'] = value.text_decoration
      }

      return CSSValue
    }

    // Mirror logic of server-side Customify_Fonts_Global::getFontStyle()
    const getFontFieldCSSCode = function (settingID, cssValue, prefix, value) {
      const fontConfig = customify.config.settings[settingID]
      let output = ''

      if (typeof window !== 'undefined' && typeof fontConfig.callback !== 'undefined' && typeof window[fontConfig.callback] === 'function') {
        // The callbacks expect a string selector right now, not a standardized list.
        // @todo Maybe migrate all callbacks to the new standardized data and remove all this.
        const plainSelectors = []
        _.each(fontConfig.selector, function (details, selector) {
          plainSelectors.push(selector)
        })
        const adjustedFontConfig = $.extend(true,{},fontConfig)
        adjustedFontConfig.selector = plainSelectors.join(', ')

        // Also, "kill" all fields unit since we pass final CSS values.
        // @todo For some reason, the client-side Typeline cbs are not consistent and expect the font-size value with unit.
        _.each(adjustedFontConfig['fields'], function(fieldValue, fieldKey) {
          if (typeof fieldValue.unit !== 'undefined') {
            adjustedFontConfig['fields'][fieldKey]['unit'] = false;
          }
        })

        // Callbacks want the value keys with underscores, not dashes.
        // We will provide them in both versions for a smoother transition.
        _.each(cssValue, function (propertyValue, property) {
          const newKey = property.replace(regexForMultipleReplace, '_')
          cssValue[newKey] = propertyValue
        })

        return window[fontConfig.callback](cssValue, adjustedFontConfig)
      }

      if (typeof fontConfig.selector === 'undefined' || _.isEmpty(fontConfig.selector) || _.isEmpty(cssValue)) {
        return output
      }

      // The general CSS allowed properties.
      const subFieldsCSSAllowedProperties = extractAllowedCSSPropertiesFromFontFields(fontConfig['fields'])

      // The selector is standardized to a list of simple string selectors, or a list of complex selectors with details.
      // In either case, the actual selector is in the key, and the value is an array (possibly empty).

      // Since we might have simple CSS selectors and complex ones (with special details),
      // for cleanliness we will group the simple ones under a single CSS rule,
      // and output individual CSS rules for complex ones.
      // Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
      const simpleCSSSelectors = []
      const complexCSSSelectors = {}

      _.each(fontConfig.selector, function (details, selector) {
        if (_.isEmpty(details.properties)) {
          // This is a simple selector.
          simpleCSSSelectors.push(selector)
        } else {
          complexCSSSelectors[selector] = details
        }
      })

      if (!_.isEmpty(simpleCSSSelectors)) {
        output += '\n' + simpleCSSSelectors.join(', ') + ' {\n'
        output += getFontFieldCSSProperties(cssValue, subFieldsCSSAllowedProperties, prefix)
        output += '}\n'
      }

      if (!_.isEmpty(complexCSSSelectors)) {
        _.each(complexCSSSelectors, function (details, selector) {
          output += '\n' + selector + ' {\n'
          output += getFontFieldCSSProperties(cssValue, details.properties, prefix)
          output += '}\n'
        })
      }

      return output
    }

    // Mirror logic of server-side Customify_Fonts_Global::getCSSProperties()
    const getFontFieldCSSProperties = function (cssValue, allowedProperties = false, prefix = '') {
      let output = ''

      $.each(cssValue, function (property, propertyValue) {
        // We don't want to output empty CSS rules.
        if ('' === propertyValue || false === propertyValue) {
          return
        }

        // If the property is not allowed, skip it.
        if (!isCSSPropertyAllowed(property, allowedProperties)) {
          return
        }

        output += prefix + property + ': ' + propertyValue + ';\n'
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

      // We will match the subfield keys with the CSS properties, but only those that properties that are allowed.
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
    const getFieldUnit = function (settingID, field) {
      if (typeof customify.config.settings[settingID] === 'undefined' || typeof customify.config.settings[settingID].fields[field] === 'undefined') {
        // These fields don't have an unit, by default.
        if (_.includes(['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'], field)) {
          return false
        }

        // The rest of the subfields have pixels as default units.
        return 'px'
      }

      if (typeof customify.config.settings[settingID].fields[field].unit !== 'undefined') {
        // Make sure that we convert all falsy unit values to the boolean false.
        return _.includes(['', 'false', false], customify.config.settings[settingID].fields[field].unit) ? false : customify.config.settings[settingID].fields[field].unit
      }

      if (typeof customify.config.settings[settingID].fields[field][3] !== 'undefined') {
        // Make sure that we convert all falsy unit values to the boolean false.
        return _.includes(['', 'false', false], customify.config.settings[settingID].fields[field][3]) ? false : customify.config.settings[settingID].fields[field][3]
      }

      return 'px'
    }

    const maybeLoadFontFamily = function (font, settingID) {
      if (typeof font.font_family === 'undefined') {
        return
      }

      const fontConfig = customify.config.settings[settingID]

      let family = font.font_family
      // The font family may be a comma separated list like "Roboto, sans"
      const fontType = customify.fontFields.determineFontType(family)

      if ('system_font' === fontType) {
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

        // Handle the font variants.
        // If there is a selected font variant and we haven't been instructed to load all, load only that,
        // otherwise load all the available variants.
        let variants =
          (
            typeof font.font_variant !== 'undefined'
            && (typeof fontConfig['fields']['font-weight']['loadAllVariants'] === 'undefined' || !fontConfig['fields']['font-weight']['loadAllVariants'])
            && typeof fontDetails.variants !== 'undefined' // If the font has no variants, any variant value we may have received should be ignored.
            && _.includes(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
          ) ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : []

        if (!_.isEmpty(variants)) {
          variants = standardizeToArray(variants)

          if (!_.isEmpty(variants)) {
            family = family + ':' + variants.map(function (variant) {
              return customify.fontFields.convertFontVariantToFVD(variant)
            }).join(',')
          }
        }

        if (fontsCache.indexOf(family) === -1) {
          WebFont.load({
            custom: {
              families: [family],
              urls: [fontDetails.src]
            },
            classes: false,
            events: false,
          })

          // Remember we've loaded this family (with it's variants) so we don't load it again.
          fontsCache.push(family)
        }
      }
      // Handle Google fonts since Web Font Loader has a special module for them.
      else if (fontType === 'google_font') {

        // Handle the font variants
        // If there is a selected font variant and we haven't been instructed to load all, load only that,
        // otherwise load all the available variants.
        let variants =
          (
            typeof font.font_variant !== 'undefined'
            && (typeof fontConfig['fields']['font-weight']['loadAllVariants'] === 'undefined' || !fontConfig['fields']['font-weight']['loadAllVariants'])
            && typeof fontDetails.variants !== 'undefined' // If the font has no variants, any variant value we may have received should be ignored.
            && _.includes(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
          ) ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : []

        if (!_.isEmpty(variants)) {
          variants = standardizeToArray(variants)

          if (!_.isEmpty(variants)) {
            family = family + ':' + variants.join(',')
          }
        }

        if (fontsCache.indexOf(family) === -1) {
          WebFont.load({
            google: {families: [family]},
            classes: false,
            events: false,
          })

          // Remember we've loaded this family (with it's variants) so we don't load it again.
          fontsCache.push(family)
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

    // This is a mirror logic of the server-side Customify_Fonts_Global::getFontFamilyFallbackStack()
    const getFontFamilyFallbackStack = function (fontFamily) {
      let fallbackStack = ''

      const fontDetails = customify.fontFields.getFontDetails(fontFamily)
      if (typeof fontDetails.fallback_stack !== 'undefined' && !_.isEmpty(fontDetails.fallback_stack)) {
        fallbackStack = fontDetails.fallback_stack
      } else if (typeof fontDetails.category !== 'undefined' && !_.isEmpty(fontDetails.category)) {
        const category = fontDetails.category
        // Search in the available categories for a match.
        if (typeof customify.fonts.categories[category] !== 'undefined') {
          // Matched by category ID/key
          fallbackStack = typeof customify.fonts.categories[category].fallback_stack !== 'undefined' ? customify.fonts.categories[category].fallback_stack : ''
        } else {
          // We need to search for aliases.
          _.find(customify.fonts.categories, function (categoryDetails) {
            if (typeof categoryDetails.aliases !== 'undefined') {
              const aliases = maybeImplodeList(categoryDetails.aliases)
              if (aliases.indexOf(category) !== -1) {
                // Found it.
                fallbackStack = typeof categoryDetails.fallback_stack !== 'undefined' ? categoryDetails.fallback_stack : ''
                return true
              }
            }

            return false
          })
        }
      }

      return fallbackStack
    }

    // Mirror logic of server-side Customify_Fonts_Global::sanitizeFontFamilyCSSValue()
    const sanitizeFontFamilyCSSValue = function (value) {
      // Since we might get a stack, attempt to treat is a comma-delimited list.
      let fontFamilies = maybeExplodeList(value)
      if (!fontFamilies.length) {
        return ''
      }

      _.each(fontFamilies, function (fontFamily, key) {
        // Make sure that the font family is free from " or ' or whitespace, at the front.
        fontFamily = fontFamily.replace(new RegExp(/^\s*["'‘’“”]*\s*/), '')
        // Make sure that the font family is free from " or ' or whitespace, at the back.
        fontFamily = fontFamily.replace(new RegExp(/\s*["'‘’“”]*\s*$/), '')

        if ('' === fontFamily) {
          delete fontFamilies[key]
          return;
        }

        // Now, if the font family contains spaces, wrap it in ".
        if (fontFamily.indexOf(' ') !== -1) {
          fontFamily = '"' + fontFamily + '"'
        }

        // Finally, put it back.
        fontFamilies[key] = fontFamily
      })

      return maybeImplodeList( fontFamilies );
    }

    const maybeExplodeList = function(str, delimiter = ',') {
      if (typeof str === 'object') {
        str = standardizeToArray(str)
      }

      // If by any chance we are given an array, just return it
      if (Array.isArray(str)) {
        return str
      }

      // Anything else we coerce to a string
      if ( typeof str !== 'string' ) {
        str = String(str);
      }

      // Make sure we trim it
      str = str.trim();

      // Bail on empty string
      if ( !str.length ) {
        return [];
      }

      // Return the whole string as an element if the delimiter is missing
      if ( str.indexOf(delimiter) === -1 ) {
        return [str];
      }

      // Explode it and return it
      return explode(delimiter, str);
    }

    const maybeImplodeList = function(value, glue = ',') {
      // If by any chance we are given a string, just return it
      if (typeof value === 'string' || typeof value === 'number') {
        return String(value)
      }

      if (typeof value === 'object') {
        value = standardizeToArray(value)
      }

      if (Array.isArray(value)) {
        return implode(glue, value)
      }

      // For anything else we return an empty string.
      return ''
    }

    const explode = function (delimiter, string, limit) {
      //  discuss at: https://locutus.io/php/explode/
      // original by: Kevin van Zonneveld (https://kvz.io)
      //   example 1: explode(' ', 'Kevin van Zonneveld')
      //   returns 1: [ 'Kevin', 'van', 'Zonneveld' ]

      if (arguments.length < 2 ||
        typeof delimiter === 'undefined' ||
        typeof string === 'undefined') {
        return null
      }
      if (delimiter === '' ||
        delimiter === false ||
        delimiter === null) {
        return false
      }
      if (typeof delimiter === 'function' ||
        typeof delimiter === 'object' ||
        typeof string === 'function' ||
        typeof string === 'object') {
        return {
          0: ''
        }
      }
      if (delimiter === true) {
        delimiter = '1'
      }

      // Here we go...
      delimiter += ''
      string += ''

      let s = string.split(delimiter)

      if (typeof limit === 'undefined') return s

      // Support for limit
      if (limit === 0) limit = 1

      // Positive limit
      if (limit > 0) {
        if (limit >= s.length) {
          return s
        }
        return s
          .slice(0, limit - 1)
          .concat([s.slice(limit - 1)
            .join(delimiter)
          ])
      }

      // Negative limit
      if (-limit >= s.length) {
        return []
      }

      s.splice(s.length + limit)
      return s
    }

    const implode = function (glue, pieces) {
      //  discuss at: https://locutus.io/php/implode/
      // original by: Kevin van Zonneveld (https://kvz.io)
      // improved by: Waldo Malqui Silva (https://waldo.malqui.info)
      // improved by: Itsacon (https://www.itsacon.net/)
      // bugfixed by: Brett Zamir (https://brett-zamir.me)
      //   example 1: implode(' ', ['Kevin', 'van', 'Zonneveld'])
      //   returns 1: 'Kevin van Zonneveld'
      //   example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'})
      //   returns 2: 'Kevin van Zonneveld'

      let i = ''
      let retVal = ''
      let tGlue = ''

      if (arguments.length === 1) {
        pieces = glue
        glue = ''
      }

      if (typeof pieces === 'object') {
        if (Object.prototype.toString.call(pieces) === '[object Array]') {
          return pieces.join(glue)
        }
        for (i in pieces) {
          retVal += tGlue + pieces[i]
          tGlue = glue
        }
        return retVal
      }

      return pieces
    }
  })
})(jQuery, window, document)
