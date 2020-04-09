;(function ($, window, document) {

  /* global customify.config */
  /* global WebFont */

  $(window).on('load',function () {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript()
  })

  const fonts_cache = []

  $(document).ready(function () {
    const api = parent.wp.customize
    const customify = parent.customify
    const apiSettings = api.settings.settings

    $.each(customify.config.settings, function (key, el) {
      const properties_prefix = typeof el.properties_prefix === 'undefined' ? '' : el.properties_prefix
      if (el.type === 'font') {
        api(key, function (setting) {
          setting.bind(function (to) {
            const rawValues = maybeJsonParse(to)

            if (typeof rawValues !== 'undefined') {
              if (typeof rawValues.font_family !== 'undefined') {
                maybeLoadFontFamily(rawValues)
              }

              const cssValues = getFontFieldCSSValues(this.id, rawValues)
              if (_.isEmpty(cssValues)) {
                return
              }
              const CSS = getFontFieldCSSCode(this.id, cssValues, properties_prefix)
              const fieldStyle = $('#customify_font_output_for_' + el.html_safe_option_id)

              fieldStyle.html(CSS)
            }
          })
        })

      } else if (typeof apiSettings !== 'undefined'
        && typeof apiSettings[key] !== 'undefined'
        && typeof el.css !== 'undefined'
        && typeof el.live !== 'undefined'
        && el.live === true) {

        api(key, function (setting) {

          setting.bind(function (to) {

            $.each(el.css, function (counter, property_config) {
              let properties = []

              properties[property_config.property] = property_config.selector
              if (typeof property_config.callback_filter !== 'undefined') {
                properties['callback'] = property_config.callback_filter
              }

              let cssUpdateArgs = {
                properties: properties,
                propertyValue: to,
                negative_value: property_config.hasOwnProperty('negative_value') ? property_config['negative_value'] : false
              }

              if (typeof this.unit !== 'undefined') {
                cssUpdateArgs.unit = this.unit
              }

              // Replace all dashes with underscores thus making the CSS property safe to us in a HTML ID.
              const regexForMultipleReplace = new RegExp('-', 'g'),
                cssStyleSelector = '.dynamic_setting_' + el.html_safe_option_id + '_property_' + property_config.property.replace(regexForMultipleReplace, '_') + '_' + counter

              $(cssStyleSelector).cssUpdate(cssUpdateArgs)
            })

          })
        })
      } else if (typeof el.live === 'object' && el.live.length > 0) {
        // if the live parameter is an object it means that is a list of css classes
        // these classes should be affected by the change of the text fields
        const fieldClass = el.live.join()

        // if this field is allowed to modify text then we'll edit this live
        if ($.inArray(el.type, ['text', 'textarea', 'ace_editor']) > -1) {
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

    /*** HELPERS **/

    const getFontFieldCSSValues = function (ID, values) {

      let store = {}

      if (typeof values.font_family !== 'undefined' && '' !== values.font_family) {
        store['font-family'] = values.font_family
      }

      if (typeof values.selected_variants !== 'undefined' && '' !== values.selected_variants) {

        let variants = ''

        if (typeof values.selected_variants !== 'undefined' && values.selected_variants !== null) {
          variants = values.selected_variants
        } else if (typeof values.variants !== 'undefined' && typeof values.variants[0] !== 'undefined') {
          variants = values.variants[0]
        }

        // google fonts also have the italic string inside, split that
        if (_.isString(variants) && variants.indexOf('italic') !== -1) {
          store['font-style'] = 'italic'
          variants = variants.replace('italic', '')
        }

        if (variants !== '') {
          if (variants === 'regular') {
            variants = 'normal'
          }

          store['font-weight'] = variants
        }
      }

      if (typeof values.font_size !== 'undefined' && '' !== values.font_size) {
        store['font-size'] = values.font_size
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.font_size)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.font_size.value !== 'undefined') {
            store['font-size'] = values.font_size.value
            if (typeof values.font_size.unit !== 'undefined') {
              store['font-size'] += values.font_size.unit
            }
          } else {
            store['font-size'] += getFieldUnit(ID, 'font-size')
          }
        } else {
          store['font-size'] += getFieldUnit(ID, 'font-size')
        }
      }

      if (typeof values.letter_spacing !== 'undefined' && '' !== values.letter_spacing) {
        store['letter-spacing'] = values.letter_spacing
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.letter_spacing)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.letter_spacing.value !== 'undefined') {
            store['letter-spacing'] = values.letter_spacing.value
            if (typeof values.letter_spacing.unit !== 'undefined') {
              store['letter-spacing'] += values.letter_spacing.unit
            }
          } else {
            store['letter-spacing'] += getFieldUnit(ID, 'letter-spacing')
          }
        } else {
          store['letter-spacing'] += getFieldUnit(ID, 'letter-spacing')
        }
      }

      if (typeof values.line_height !== 'undefined' && '' !== values.line_height) {
        store['line-height'] = values.line_height
        // If the value already contains a unit (is not numeric), go with that.
        if (isNaN(values.line_height)) {
          // If we have a standardized value field (as array), use that.
          if (typeof values.line_height.value !== 'undefined') {
            store['line-height'] = values.line_height.value
            if (typeof values.line_height.unit !== 'undefined') {
              store['line-height'] += values.line_height.unit
            }
          } else {
            store['line-height'] += getFieldUnit(ID, 'line-height')
          }
        } else {
          store['line-height'] += getFieldUnit(ID, 'line-height')
        }
      }

      if (typeof values.text_align !== 'undefined' && '' !== values.text_align) {
        store['text-align'] = values.text_align
      }

      if (typeof values.text_transform !== 'undefined' && '' !== values.text_transform) {
        store['text-transform'] = values.text_transform
      }
      if (typeof values.text_decoration !== 'undefined' && '' !== values.text_decoration) {
        store['text-decoration'] = values.text_decoration
      }

      return store
    }

    const getFontFieldCSSCode = function (ID, values, prefix) {

      // This whole logic is a replica of the server side logic found in Customify_Fonts_Global::get_font_style()

      const field = customify.config.settings[ID]
      let output = ''

      if (typeof window !== 'undefined' && typeof field.callback !== 'undefined' && typeof window[field.callback] === 'function') {
        output = window[field.callback](values, field)
      } else {
        if (typeof field.selector === 'undefined' || _.isEmpty(field.selector)) {
          return output
        }

        // The selector is standardized to a list of simple string selectors, or a list of complex selectors with details.
        // In either case, the actual selector is in the key, and the value is an array (possibly empty).

        // Since we might have simple CSS selectors and complex ones (with special details),
        // for cleanliness we will group the simple ones under a single CSS rule,
        // and output individual CSS rules for complex ones.
        // Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
        const simpleCSSSelectors = [];
        const complexCSSSelectors = {};

        _.each(field.selector, function (details, selector) {
          if (_.isEmpty(details.properties)) {
            // This is a simple selector.
            simpleCSSSelectors.push(selector)
          } else {
            complexCSSSelectors[selector] = details
          }
        })

        if ( !_.isEmpty(simpleCSSSelectors)) {
          output += '\n' + simpleCSSSelectors.join(', ') + ' {\n'
          output += getFontFieldCSSProperties(values, [], prefix)
          output += '}\n'
        }

        if ( !_.isEmpty(complexCSSSelectors)) {
          _.each(complexCSSSelectors, function (details, selector) {
            output += '\n' + selector + ' {\n'
            output += getFontFieldCSSProperties(values, details.properties, prefix)
            output += '}\n'
          })
        }
      }

      return output
    }

    const getFontFieldCSSProperties = function (values, allowedProperties = false, prefix = '') {
      let output = ''

      $.each(values, function (property, value) {
        // We don't want to output empty CSS rules.
        if (value === '') {
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

    const isCSSPropertyAllowed = function (property, allowedProperties = false) {
      // Empty properties are not allowed.
      if (_.isEmpty(property)) {
        return false
      }

      // Everything is allowed if nothing is specified.
      if (_.isEmpty(allowedProperties)) {
        return true;
      }

      if (_.contains(allowedProperties, property)) {
        return true
      }

      if (_.has(allowedProperties, property) && allowedProperties[property]) {
        return true
      }

      return false
    }

    const getFieldUnit = function (ID, field) {
      let unit = ''
      if (typeof customify.config.settings[ID] === 'undefined' || typeof customify.config.settings[ID].fields[field] === 'undefined') {
        return unit
      }

      if (typeof customify.config.settings[ID].fields[field].unit !== 'undefined') {
        return customify.config.settings[ID].fields[field].unit
      } else if (typeof customify.config.settings[ID].fields[field][3] !== 'undefined') {
        // in case of an associative array
        return customify.config.settings[ID].fields[field][3]
      }

      return unit
    }

    const maybeLoadFontFamily = function (font) {
      if (typeof font.font_family === 'undefined') {
        return
      }

      let family = font.font_family
      // The font family may be a comma separated list like "Roboto, sans"

      const fontType = customify.fontFields.determineFontType(family)

      // Handle theme defined fonts and cloud fonts together since they are very similar.
      if (fontType === 'theme_font' || fontType === 'cloud_font') {


        if (typeof font.src === 'undefined') {
          let fontsArray

          if (fontType === 'theme_font') {
            fontsArray = Object.keys(customify.config.theme_fonts).map(key => customify.config.theme_fonts[key])
          } else {
            fontsArray = Object.keys(customify.config.cloud_fonts).map(key => customify.config.cloud_fonts[key])
          }

          const index = fontsArray.findIndex(fontObj => fontObj.family === family)
          if (index > -1) {
            font.src = fontsArray[index].src
          }
        }

        // Bail if we have no src.
        if (typeof font.src === 'undefined') {
          return
        }

        // Handle the font variants
        if (typeof font.variants !== 'undefined') {
          let variants = maybeJsonParse(font.variants)

          // Standardize the variants to an array.
          if (typeof variants === 'string' || typeof variants === 'number') {
            variants = [variants]
          } else if (typeof variants === 'object') {
            variants = Object.values(variants);
          }

          family = family + ':' + variants.map(function (variant) {
            return customify.fontFields.convertFontVariantToFVD(variant)
          }).join(',')
        }

        if (fonts_cache.indexOf(family) === -1) {
          setTimeout(function () {
            WebFont.load({
              custom: {
                families: [family],
                urls: [font.src]
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
          }, 10)

          // Remember we've loaded this family (with it's variants) so we don't load it again.
          fonts_cache.push(family)
        }
      }
      // Handle Google fonts since Web Font Loader has a special module for them.
      else if (fontType === 'google') {
        let variants = null,
          subsets = null

        if (typeof font.variants !== 'undefined') {
          variants = maybeJsonParse(font.variants)

          if (typeof variants === 'string' || typeof variants === 'number') {
            variants = [variants]
          } else if (typeof variants === 'object') {
            variants = Object.values(variants);
          }

          family = family + ':' + variants.join(',')
        }

        if (typeof font.selected_subsets !== 'undefined') {
          subsets = maybeJsonParse(font.selected_subsets)

          if (typeof subsets === 'string' || typeof subsets === 'number') {
            subsets = [subsets]
          } else if (typeof subsets === 'object') {
            subsets = Object.values(subsets);
          }

          family = family + ':' + subsets.join(',')
        }

        if (fonts_cache.indexOf(family) === -1) {
          setTimeout(function () {
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
          }, 10)

          // Remember we've loaded this family (with it's variants and subsets) so we don't load it again.
          fonts_cache.push(family)
        }

      } else {
        // Maybe Typekit, Fonts.com or Fontdeck fonts
      }
    }

    const maybeJsonParse = function (value) {
      let parsed

      if (typeof value !== 'string') {
        return value
      }

      //try and parse it, with decodeURIComponent
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
