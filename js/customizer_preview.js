;(function ($, window, document, undefined) {

  /* global customify_settings */
  /* global WebFont */

  $(window).load(function () {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript()
  })

  const fonts_cache = []

  $(document).ready(function () {
    const api = parent.wp.customize
    const apiSettings = api.settings.settings

    $.each(customify_settings.settings, function (key, el) {
      const properties_prefix = typeof el.properties_prefix === 'undefined' ? '' : el.properties_prefix
      if (el.type === 'font') {
        api(key, function (setting) {
          setting.bind(function (to) {
            const $values = maybeJsonParse(to)

            if (typeof $values !== 'undefined') {
              if (typeof $values.font_family !== 'undefined') {
                maybeLoadFontFamily($values)
              }

              const vls = getCSSValues(this.id, $values)
              if (_.isEmpty(vls)) {
                return
              }
              const CSS = getCSSCode(this.id, vls, properties_prefix)
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

    const getCSSValues = function (ID, values) {

      let store = {}

      if (typeof values.font_family !== 'undefined') {
        store['font-family'] = values.font_family
      }

      if (typeof values.selected_variants !== 'undefined') {

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

      if (typeof values.font_size !== 'undefined') {
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

      if (typeof values.letter_spacing !== 'undefined') {
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

      if (typeof values.line_height !== 'undefined') {
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

      if (typeof values.text_align !== 'undefined') {
        store['text-align'] = values.text_align
      }

      if (typeof values.text_transform !== 'undefined') {
        store['text-transform'] = values.text_transform
      }
      if (typeof values.text_decoration !== 'undefined') {
        store['text-decoration'] = values.text_decoration
      }

      return store
    }

    const getCSSCode = function (ID, values, prefix) {
      const field = customify_settings.settings[ID]
      let output = ''

      if (typeof window !== 'undefined' && typeof field.callback !== 'undefined' && typeof window[field.callback] === 'function') {
        output = window[field.callback](values, field)
      } else {
        output = field.selector + '{\n'
        $.each(values, function (k, v) {
          output += prefix + k + ': ' + v + ';\n'
        })
        output += '}\n'
      }

      return output
    }

    const getFieldUnit = function (ID, field) {
      let unit = ''
      if (typeof customify_settings.settings[ID] === 'undefined' || typeof customify_settings.settings[ID].fields[field] === 'undefined') {
        return unit
      }

      if (typeof customify_settings.settings[ID].fields[field].unit !== 'undefined') {
        return customify_settings.settings[ID].fields[field].unit
      } else if (typeof customify_settings.settings[ID].fields[field][3] !== 'undefined') {
        // in case of an associative array
        return customify_settings.settings[ID].fields[field][3]
      }

      return unit
    }

    const maybeLoadFontFamily = function (font) {
      if (typeof font.font_family === 'undefined') {
        return
      }

      const fontType = determineFontType(font.font_family)
      let family = font.font_family

      // Handle theme defined fonts and cloud fonts together since they are very similar.
      if (fontType === 'theme_font' || fontType === 'cloud_font') {


        if (typeof font.src === 'undefined') {
          let fontsArray

          if (fontType === 'theme_font') {
            fontsArray = Object.keys(customify_settings.theme_fonts).map(key => customify_settings.theme_fonts[key])
          } else {
            fontsArray = Object.keys(customify_settings.cloud_fonts).map(key => customify_settings.cloud_fonts[key])
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
            return convertFontVariantToFVD(variant)
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
      tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'
      tk.type = 'text/javascript'
      let s = document.getElementsByTagName('script')[0]
      s.parentNode.insertBefore(tk, s)
    }
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
})(jQuery, window, document)
