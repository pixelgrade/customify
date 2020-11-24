/** @namespace customify */
window.customify = window.customify || parent.customify || {};

(function ($, customify, wp) {

  /**
   * Expose the API publicly on window.customify.colorPalettes
   *
   * @namespace customify.colorPalettes
   */
  if ( typeof customify.colorPalettes === 'undefined' ) {
    customify.colorPalettes = {}
  }
  _.extend( customify.colorPalettes, function () {
    const api = wp.customize
    let apiSettings

    const defaultVariation = 'light'

    const filteredColors = {}

    const primaryColorSelector = '#_customize-input-sm_dark_color_primary_slider_control'
    const secondaryColorSelector = '#_customize-input-sm_dark_color_secondary_slider_control'
    const tertiaryColorSelector = '#_customize-input-sm_dark_color_tertiary_slider_control'
    const colorSlidersSelector = primaryColorSelector + ', ' + secondaryColorSelector + ', ' + tertiaryColorSelector

    let setupGlobalsDone = false

    const setupGlobals = () => {

      if (setupGlobalsDone) {
        return
      }

      // Initialize filtered colors global.
      _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
        filteredColors[settingID] = ''
      })

      // Cache initial settings configuration to be able to update connected fields on variation change.
      if (typeof customify.settingsClone === 'undefined') {
        customify.settingsClone = $.extend(true, {}, apiSettings)
      }

      // Create a stack of callbacks bound to parent settings to be able to unbind them
      // when altering the connected_fields attribute.
      if (typeof customify.colorPalettes.connectedFieldsCallbacks === 'undefined') {
        customify.colorPalettes.connectedFieldsCallbacks = {}
      }

      setupGlobalsDone = true
    }

    const hexDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f']

    function hex (x) {
      return isNaN(x) ? '00' : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16]
    }

    function rgb2hex (color) {
      return '#' + hex(color[0]) + hex(color[1]) + hex(color[2])
    }

    function hsl2hex (color) {
      const rgb = hsl2Rgb(color.hue, color.saturation, color.lightness)
      return rgb2hex(rgb)
    }

    function hex2rgba (hex) {
      const matches = /^#([A-Fa-f0-9]{3,4}){1,2}$/.test(hex)
      let r = 0, g = 0, b = 0, a = 0
      if (matches) {
        hex = hex.substring(1).split('')
        if (hex.length === 3) {
          hex = [hex[0], hex[0], hex[1], hex[1], hex[2], hex[2], 'F', 'F']
        }
        if (hex.length === 4) {
          hex = [hex[0], hex[0], hex[1], hex[1], hex[2], hex[2], hex[3], hex[3]]
        }
        r = parseInt([hex[0], hex[1]].join(''), 16)
        g = parseInt([hex[2], hex[3]].join(''), 16)
        b = parseInt([hex[4], hex[5]].join(''), 16)
        a = parseInt([hex[6], hex[7]].join(''), 16)
      }
      const hsl = rgbToHsl(r, g, b)
      return {
        red: r,
        green: g,
        blue: b,
        alpha: a,
        hue: hsl[0],
        saturation: hsl[1],
        lightness: hsl[2],
        luma: 0.2126 * r + 0.7152 * g + 0.0722 * b
      }
    }

    function rgbToHsl (r, g, b) {
      r /= 255
      g /= 255
      b /= 255
      const max = Math.max(r, g, b), min = Math.min(r, g, b)
      let h, s, l = (max + min) / 2

      if (max == min) {
        h = s = 0 // achromatic
      } else {
        const d = max - min
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
        switch (max) {
          case r:
            h = (g - b) / d + (g < b ? 6 : 0)
            break
          case g:
            h = (b - r) / d + 2
            break
          case b:
            h = (r - g) / d + 4
            break
        }
        h /= 6
      }
      return [h, s, l]
    }

    const resetSettings = () => {
      _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
        const setting = api(settingID)

        if (typeof setting !== 'undefined') {
          let parentSettingData = apiSettings[settingID]

          const finalValue = getFilteredColor(settingID)

          _.each(parentSettingData.connected_fields, function (connectedFieldData) {
            if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
              return
            }
            const connectedSetting = api(connectedFieldData.setting_id)
            if (_.isUndefined(connectedSetting)) {
              return
            }

            connectedSetting.set(finalValue)
          })

          // Also set the final setting value, for safe keeping.
          const finalSettingID = settingID + '_final'
          const finalSetting = api(finalSettingID)
          if (!_.isUndefined(finalSetting)) {
            if (!_.isUndefined(parentSettingData.connected_fields) && !_.isEmpty(parentSettingData.connected_fields)) {
              finalSetting.set(finalValue)
            } else {
              finalSetting.set('')
            }
          }
        }
      })
    }

    const updateFilteredColors = () => {
      _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
        const setting = api(settingID)

        if (typeof setting !== 'undefined') {
          const value = setting()
          filteredColors[settingID] = filterColor(value)
        }
      })
    }

    const getFilteredColor = (settingID) => {
      return filteredColors[settingID]
    }

    const getMasterFieldCallback = function (parentSettingData, parentSettingID) {
      return function (newValue, oldValue) {
        const finalValue = getFilteredColor(parentSettingID)

        _.each(parentSettingData.connected_fields, function (connectedFieldData) {
          if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
            return
          }
          const setting = api(connectedFieldData.setting_id)
          if (_.isUndefined(setting)) {
            return
          }

          setting.set(finalValue)
        })
        updateFilterPreviews()

        // Also set the final setting value, for safe keeping.
        const finalSettingID = parentSettingID + '_final'
        const finalSetting = api(finalSettingID)
        if (!_.isUndefined(finalSetting)) {
          if (!_.isUndefined(parentSettingData.connected_fields) && !_.isEmpty(parentSettingData.connected_fields)) {
            finalSetting.set(finalValue)
          } else {
            finalSetting.set('')
          }
        }
      }
    }

    const bindConnectedFields = function () {
      _.each(customify.colorPalettes.masterSettingIds, function (parentSettingID) {
        if (typeof apiSettings[parentSettingID] !== 'undefined') {
          const parentSettingData = apiSettings[parentSettingID]
          const parentSetting = api(parentSettingID)

          if (!_.isUndefined(parentSettingData.connected_fields)) {
            customify.colorPalettes.connectedFieldsCallbacks[parentSettingID] = getMasterFieldCallback(parentSettingData, parentSettingID)
            parentSetting.bind(customify.colorPalettes.connectedFieldsCallbacks[parentSettingID])

            _.each(parentSettingData.connected_fields, function (connectedFieldData) {
              const connectedSettingID = connectedFieldData.setting_id
              const connectedSetting = api(connectedSettingID)

              if (typeof connectedSetting !== 'undefined') {
                customify.colorPalettes.connectedFieldsCallbacks[connectedSettingID] = toggleAlteredClassOnMasterControls
                connectedSetting.bind(customify.colorPalettes.connectedFieldsCallbacks[connectedSettingID])
              }
            })
          }
        }
      })
    }

    const unbindConnectedFields = function () {
      _.each(customify.colorPalettes.connectedFieldsCallbacks, function (callback, settingID) {
        const setting = api(settingID)
        setting.unbind(callback)
      })
      customify.colorPalettes.connectedFieldsCallbacks = {}
    }

    // alter connected fields of the master colors controls depending on the selected palette variation
    const getCurrentVariation = () => {
      const setting = api('sm_color_palette_variation')

      if (_.isUndefined(setting)) {
        return defaultVariation
      }

      const variation = setting()

      if (!customify.colorPalettes.variations.hasOwnProperty(variation)) {
        return defaultVariation
      }

      return variation
    }

    const getSwapMap = (variation) => {
      if (!customify.colorPalettes.variations.hasOwnProperty(variation)) {
        return defaultVariation
      }

      return customify.colorPalettes.variations[variation]
    }

    // return an array with the hex values of the current palette
    const getCurrentPaletteColors = () => {
      const colors = []
      _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
        const setting = api(settingID)
        const color = setting()
        colors.push(color)
      })
      return colors
    }

    function hsl2Rgb (h, s, l) {
      let r, g, b

      if (s == 0) {
        r = g = b = l // achromatic
      } else {
        const hue2rgb = function hue2rgb (p, q, t) {
          if (t < 0) t += 1
          if (t > 1) t -= 1
          if (t < 1 / 6) return p + (q - p) * 6 * t
          if (t < 1 / 2) return q
          if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6
          return p
        }

        const q = l < 0.5 ? l * (1 + s) : l + s - l * s
        const p = 2 * l - q
        r = hue2rgb(p, q, h + 1 / 3)
        g = hue2rgb(p, q, h)
        b = hue2rgb(p, q, h - 1 / 3)
      }

      return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)]
    }

    function mixRGB (color1, color2, ratio) {
      ratio = ratio || 0.5
      color1.red = parseInt(color2.red * ratio + color1.red * (1 - ratio), 10)
      color1.green = parseInt(color2.green * ratio + color1.green * (1 - ratio), 10)
      color1.blue = parseInt(color2.blue * ratio + color1.blue * (1 - ratio), 10)
      return hex2rgba(rgb2hex([color1.red, color1.green, color1.blue]))
    }

    function mix (property, color1, color2, ratio) {
      return color1[property] * (1 - ratio) + color2[property] * ratio
    }

    function mixValues (value1, value2, ratio) {
      return value1 * (1 - ratio) + value2 * ratio
    }

    const filterColor = (color, filter) => {
      filter = typeof filter === 'undefined' ? $('[name*="sm_palette_filter"]:checked').val() : filter

      let newColor = hex2rgba(color)
      const palette = getCurrentPaletteColors()
      const paletteColors = palette.slice(0, 3)
      const paletteDark = palette.slice(3, 6)
      const average = getAveragePixel(getPixelsFromColors(palette))
      const averageColor = getAveragePixel(getPixelsFromColors(paletteColors))
      const averageDark = getAveragePixel(getPixelsFromColors(paletteDark))

      // Intensity Filters
      if (filter === 'vivid') {
        newColor = hsl2Rgb(newColor.hue, mixValues(newColor.saturation, 1, 0.5), newColor.lightness)
        return rgb2hex(newColor)
      }

      if (filter === 'warm' && color !== palette[0]) {
        let sepia = hex2rgba('#704214')
        sepia.saturation = mix('saturation', sepia, newColor, 1)
        sepia.lightness = mix('lightness', sepia, newColor, 1)
        sepia = hex2rgba(hsl2hex(sepia))
        newColor.saturation = newColor.saturation * 0.75
        newColor = hex2rgba(hsl2hex(newColor))
        newColor = mixRGB(newColor, sepia, 0.75)

        newColor.lightness = mix('lightness', newColor, hex2rgba(newColor.lightness > 0.5 ? '#FFF' : '#000'), 0.2)
        return hsl2hex(newColor)
      }

      if (filter === 'softer') {
        // if ( paletteColors.indexOf( color ) !== -1 ) {
        //     newColor = mixRGB( newColor, averageColor, 0.5 );
        //     return rgb2hex( [ newColor.red, newColor.green, newColor.blue ] );
        // }
        newColor.saturation = mix('saturation', newColor, hex2rgba('#FFF'), 0.3)
        newColor.lightness = mix('lightness', newColor, hex2rgba('#FFF'), 0.1)
        // newColor.hue = mix( 'hue', newColor, averageColor, 1 );
        return hsl2hex(newColor)
      }

      if (filter === 'pastel') {
        newColor.saturation = mix('saturation', newColor, hex2rgba('#FFF'), 0.6)
        newColor.lightness = mix('lightness', newColor, hex2rgba('#FFF'), 0.2)
        return hsl2hex(newColor)
      }

      if (filter === 'greyish') {
        newColor = hsl2Rgb(newColor.hue, mixValues(newColor.saturation, 0, 0.8), newColor.lightness)
        return rgb2hex(newColor)
      }

      // Custom (Real) Filters
      if (filter === 'clarendon') {
        // Color Group
        // Slightly increase saturation
        if (color === palette[0] || color === palette[1] || color === palette[2]) {
          newColor = hsl2Rgb(newColor.hue, mixValues(newColor.saturation, 1, 0.3), newColor.lightness)
          return rgb2hex(newColor)
        }

        // Dark Group
        // Add dark to darker colors
        if (color === palette[3] || color === palette[4] || color === palette[5]) {
          newColor.lightness = mix('lightness', newColor, hex2rgba('#000'), 0.4)
        }

        // Light Group
        // Add light to lighter colors
        if (color === palette[6] || color === palette[7] || color === palette[8]) {
          newColor.lightness = mix('lightness', newColor, hex2rgba('#FFF'), 0.4)
        }

        return hsl2hex(newColor)
      }

      // Inactive Below
      if (filter === 'cold' && color !== palette[0]) {
        const targetHue = 0.55

        newColor.saturation = mix('saturation', newColor, hex2rgba('#FFF'), 0.4)
        newColor.hue = (newColor.hue - targetHue) / 18 + targetHue
        newColor = hex2rgba(hsl2hex(newColor))

        // increase contrast ( saturation +10%, lightness +/- 20% );
        const newColorHSL = rgbToHsl(newColor.red, newColor.green, newColor.blue)
        newColor.hue = newColorHSL[0]
        newColor.saturation = mixValues(newColorHSL[1], 1, 0.1)
        newColor.lightness = mix('lightness', newColor, hex2rgba(newColor.lightness > 0.5 ? '#FFF' : '#000'), 0.2)
        return hsl2hex(newColor)
      }

      if (filter === 'dumb') {

        if (color === palette[1] || color === palette[2]) {
          newColor = hex2rgba(palette[0])
          newColor.lightness = mix('lightness', newColor, hex2rgba('#000'), 0.2)
          newColor.saturation = mix('saturation', newColor, hex2rgba('#000'), 0.2)

          if (color === palette[2]) {
            newColor.lightness = mix('lightness', newColor, hex2rgba('#000'), 0.2)
            newColor.saturation = mix('saturation', newColor, hex2rgba('#000'), 0.2)
          }
          return hsl2hex(newColor)
        } else {
          newColor.hue = hex2rgba(palette[0]).hue
          return hsl2hex(newColor)
        }
      }

      if (filter === 'mayfair') {
        if (color === palette[1] || color === palette[2]) {
          newColor = hex2rgba(palette[0])
          newColor.hue = (newColor.hue + 0.05) % 1
          if (color === palette[2]) {
            newColor.hue = (newColor.hue + 0.05) % 1
          }
          return hsl2hex(newColor)
        } else {
          newColor.hue = hex2rgba(palette[0]).hue
          return hsl2hex(newColor)
        }
      }

      if (filter === 'sierra') {
        if (color === palette[1] || color === palette[2]) {
          newColor = hex2rgba(palette[0])
          newColor.hue = (newColor.hue + 0.95) % 1
          if (color === palette[2]) {
            newColor.hue = (newColor.hue + 0.95) % 1
          }
          return hsl2hex(newColor)
        } else {
          newColor.hue = hex2rgba(palette[0]).hue
          return hsl2hex(newColor)
        }
      }

      return color
    }

    const createCurrentPaletteControls = () => {
      const $palette = $('.c-color-palette')
      const $fields = $palette.find('.c-color-palette__fields').find('input')

      if (!$palette.length) {
        return
      }

      const $colors = $palette.find('.sm-color-palette__color')

      $colors.each((i, obj) => {
        const $obj = $(obj)
        const settingID = $obj.data('setting')
        const $input = $fields.filter('.' + settingID)
        const setting = api(settingID)

        $obj.data('target', $input)

        if ($obj.hasClass('js-no-picker')) { return }

        $input.iris({
          change: (event, ui) => {
            const currentColor = ui.color.toString()

            $obj.css('color', filterColor(currentColor))

            filteredColors[settingID] = filterColor(currentColor)
            setting.set(currentColor)

            if (event.originalEvent.type !== 'external') {
              $palette.find('.sm-color-palette__color.' + settingID).removeClass('altered')
            }

            setPalettesOnConnectedFields()
          },
        })

        $obj.find('.iris-picker').on('click', function (e) {
          e.stopPropagation()
          e.preventDefault()
        })

        const showColorPicker = () => {
          $colors.not($obj).each(function (i, obj) {
            $(obj).data('target').not($input).hide()
          })
          $input.show().focus()
        }

        $obj.on('click', (e) => {
          e.stopPropagation()
          e.preventDefault()

          if ($input.is(':visible')) {
            $input.iris('hide')
            $input.hide()
            $colors.removeClass('active inactive')
          } else {
            if ($obj.is('.altered')) {
              confirmChanges(showColorPicker)
            } else {
              showColorPicker()
            }
          }
        })

        $input.on('click', (e) => {
          e.stopPropagation()
          e.preventDefault()
        })

        $input.on('focus', (e) => {
          $colors.not($obj).addClass('inactive').removeClass('active')
          $obj.addClass('active').removeClass('inactive')

          $colors.not($obj).each(function (i, other) {
            $(other).data('target').iris('hide')
          })

          const $iris = $input.next('.iris-picker')
          const paletteWidth = $palette.find('.c-color-palette__colors').outerWidth()
          const $visibleColors = $colors.filter(':visible')
          const index = $visibleColors.index($obj)

          $iris.css('left', (paletteWidth - 200) * index / ($visibleColors.length - 1))

          showOldColors()

          $input.iris('show')
        })

        $input.on('focusout', (e) => {
          showNewColors()
        })
      })

      $('body').on('click', function () {
        $colors.removeClass('active inactive')
        $colors.each(function (i, obj) {
          const $input = $(obj).data('target')

          if (!$(obj).hasClass('js-no-picker')) {
            $input.iris('hide')
          }
          $input.hide()
        })
      })
    }

    const showNewColors = function () {
      _.each(customify.colorPalettes.masterSettingIds, function (id) {
        $('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', getFilteredColor(id))
      })
    }

    const showOldColors = function () {
      _.each(customify.colorPalettes.masterSettingIds, function (id) {
        const setting = api(id)
        const initialColor = setting()
        $('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', initialColor)
      })
    }

    const onPaletteChange = function () {
      $(this).trigger('customify:preset-change')
      updateFilteredColors()
      reinitializeConnectedFields()
    }

    // this function goes through all the connected fields and adds swatches to the default color picker for all the colors in the current color palette
    const setPalettesOnConnectedFields = _.debounce(() => {
      let $targets = $()
      // loop through the master settings
      _.each(customify.colorPalettes.masterSettingIds, function (parentSettingID) {
        if (typeof apiSettings[parentSettingID] !== 'undefined') {
          const parentSettingData = apiSettings[parentSettingID]

          if (!_.isUndefined(parentSettingData.connected_fields)) {
            // loop through all the connected fields and search the element on which the iris plugin has been initialized
            _.each(parentSettingData.connected_fields, function (connectedFieldData) {
              // the connected_setting_id is different than the actual id attribute of the element we're searching for
              // so we have to do some regular expressions
              const connectedSettingID = connectedFieldData.setting_id
              const matches = connectedSettingID.match(/\[(.*?)\]/)

              if (matches) {
                const targetID = matches[1]
                const $target = $('.customize-control-color').filter('[id*="' + targetID + '"]').find('.wp-color-picker')
                $targets = $targets.add($target)
              }
            })
          }
        }
      })
      // apply the current color palettes to all the elements found
      $targets.iris({palettes: getCurrentPaletteColors()})
    }, 30)

    const toggleAlteredClassOnMasterControls = _.debounce(() => {
      const alteredSettings = []
      let alteredSettingsSelector

      _.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
        let connectedFields = apiSettings[masterSettingId]['connected_fields']
        const masterSettingValue = api(masterSettingId)()
        let connectedFieldsWereAltered = false

        if (!_.isUndefined(connectedFields) && !Array.isArray(connectedFields)) {
          connectedFields = Object.keys(connectedFields).map(function (key) {
            return connectedFields[key]
          })
        }

        if (!_.isUndefined(connectedFields) && connectedFields.length) {
          _.each(connectedFields, function (connectedField) {
            const connectedSettingId = connectedField.setting_id
            const connectedSetting = api(connectedSettingId)

            if (typeof connectedSetting !== 'undefined') {
              const connectedFieldValue = connectedSetting()

              if (typeof connectedFieldValue === 'string' && connectedFieldValue.toLowerCase() !== filterColor(masterSettingValue).toLowerCase()) {
                connectedFieldsWereAltered = true
              }
            }
          })

          if (connectedFieldsWereAltered) {
            alteredSettings.push(masterSettingId)
          }
        }
      })

      alteredSettingsSelector = '.' + alteredSettings.join(', .')

      $('.c-color-palette .color').removeClass('altered')

      if (alteredSettings.length) {
        $('.c-color-palette .color').filter(alteredSettingsSelector).addClass('altered')
      }

    }, 30)

    const toggleHiddenClassOnMasterControls = _.debounce(() => {
      const optionsToShow = []
      let optionsSelector

      _.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
        const connectedFields = apiSettings[masterSettingId]['connected_fields']

        if (!_.isUndefined(connectedFields) && !_.isEmpty(connectedFields)) {
          optionsToShow.push(masterSettingId)
        }
      })

      if (!_.isEmpty(optionsToShow)) {
        optionsSelector = '.' + optionsToShow.join(', .')
      } else {
        optionsSelector = '*'
      }

      $('.sm-palette-filter .color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
      $('.sm-color-palette__color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
      $('.js-color-palette .palette__item').addClass('hidden').filter(optionsSelector).removeClass('hidden')
    }, 30)

    const refreshCurrentPaletteControl = () => {
      toggleAlteredClassOnMasterControls()
      toggleHiddenClassOnMasterControls()
      setPalettesOnConnectedFields()
      showNewColors()
    }

    const swapConnectedFields = (settings, swapMap) => {
      // @todo This is weird. We should be able to have the settings in the proper form.
      const newSettings = JSON.parse(JSON.stringify(settings))
      const oldSettings = JSON.parse(JSON.stringify(settings))

      _.each(swapMap, function (fromArray, to) {
        if (typeof newSettings[to] !== 'undefined') {
          let newConnectedFields = []
          if (fromArray instanceof Array) {
            _.each(fromArray, function (from) {
              let oldConnectedFields
              if (_.isUndefined(oldSettings[from]['connected_fields'])) {
                oldSettings[from]['connected_fields'] = []
              }
              oldConnectedFields = Object.values(oldSettings[from]['connected_fields'])
              newConnectedFields = newConnectedFields.concat(oldConnectedFields)
            })
          }
          newSettings[to]['connected_fields'] = Object.keys(newConnectedFields).map(function (key) {
            return newConnectedFields[key]
          })
        }
      })
      return _.clone(newSettings)
    }

    const moveConnectedFields = (oldSettings, from, to, ratio) => {

      const settings = _.clone(oldSettings)

      if (!_.isUndefined(settings[to]) && !_.isUndefined(settings[from])) {

        if (_.isUndefined(settings[from]['connected_fields'])) {
          settings[from]['connected_fields'] = []
        }

        if (_.isUndefined(settings[to]['connected_fields'])) {
          settings[to]['connected_fields'] = []
        }

        const oldFromConnectedFields = Object.values(settings[from]['connected_fields'])
        const oldToConnectedFields = Object.values(settings[to]['connected_fields'])
        const oldConnectedFields = oldToConnectedFields.concat(oldFromConnectedFields)
        const count = Math.round(ratio * oldConnectedFields.length)

        let newToConnectedFields = oldConnectedFields.slice(0, count)
        const newFromConnectedFields = oldConnectedFields.slice(count)

        newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
          return newToConnectedFields[key]
        })
        newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
          return newToConnectedFields[key]
        })

        settings[to]['connected_fields'] = newToConnectedFields
        settings[from]['connected_fields'] = newFromConnectedFields
      }

      return settings
    }

    const reloadConnectedFields = () => {
      let tempSettings = JSON.parse(JSON.stringify(customify.settingsClone))
      const diversityOptions = $('[name*="sm_color_diversity"]')
      const colorationOptions = $('[name*="sm_coloration_level"]')

      const hasDiversityOption = !!diversityOptions.length
      const isDefaultDiversitySelected = typeof diversityOptions.filter(':checked').data('default') !== 'undefined'
      const isDefaultDiversity = hasDiversityOption ? isDefaultDiversitySelected : true

      const hasColorationOptions = !!colorationOptions.length
      const isDefaultColorationSelected = typeof $('[name*="sm_coloration_level"]:checked').data('default') !== 'undefined'
      const isDefaultColoration = hasColorationOptions ? isDefaultColorationSelected : true

      const selectedDiversity = hasDiversityOption ? $('[name*="sm_color_diversity"]:checked').val() : api('sm_color_diversity')()

      if (!isDefaultDiversity || !isDefaultColoration) {
        const primaryRatio = $(primaryColorSelector).val() / 100
        const secondaryRatio = $(secondaryColorSelector).val() / 100
        const tertiaryRatio = $(tertiaryColorSelector).val() / 100

        tempSettings = moveConnectedFields(tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio)
        tempSettings = moveConnectedFields(tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio)
        tempSettings = moveConnectedFields(tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio)

        const diversity_variation = getSwapMap('color_diversity_low')
        tempSettings = swapConnectedFields(tempSettings, diversity_variation)

        if (selectedDiversity === 'medium') {
          tempSettings = moveConnectedFields(tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.5)
        }

        if (selectedDiversity === 'high') {
          tempSettings = moveConnectedFields(tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.67)
          tempSettings = moveConnectedFields(tempSettings, 'sm_color_secondary', 'sm_color_tertiary', 0.50)
        }
      }

      const shuffle = $('[name*="sm_shuffle_colors"]:checked').val()
      if (shuffle !== 'default') {
        const shuffleVariation = getSwapMap('shuffle_' + shuffle)
        tempSettings = swapConnectedFields(tempSettings, shuffleVariation)
      }

      const dark_mode = $('[name*="sm_dark_mode_control"]:checked').val()
      if (dark_mode === 'on') {
        const dark_mmode_variation = getSwapMap('dark')
        tempSettings = swapConnectedFields(tempSettings, dark_mmode_variation)
      }

      _.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
        apiSettings[masterSettingId] = tempSettings[masterSettingId]
      })
    }

    const getPixelsFromColors = function (colors) {
      const pixels = []
      _.each(colors, function (color) {
        pixels.push(hex2rgba(color))
      })
      return pixels
    }

    const getAveragePixel = function (pixels) {
      const averagePixel = {
        red: 0,
        green: 0,
        blue: 0,
        alpha: 0,
        hue: 0,
        saturation: 0,
        lightness: 0,
        luma: 0
      }

      for (let i = 0; i < pixels.length; i++) {
        const pixel = pixels[i]

        for (let k in averagePixel) {
          averagePixel[k] += pixel[k]
        }
      }

      for (let k in averagePixel) {
        averagePixel[k] /= pixels.length
      }

      return averagePixel
    }

    const applyColorationValueToFields = () => {
      const coloration = $('[name*="sm_coloration_level"]:checked').val()

      if (typeof $('[name*="sm_coloration_level"]:checked').data('default') !== 'undefined') {

        const sliders = ['sm_dark_color_primary_slider', 'sm_dark_color_secondary_slider', 'sm_dark_color_tertiary_slider']
        _.each(sliders, function (sliderID) {
          const sliderSetting = customify.config.settings[sliderID]
          api(sliderID).set(sliderSetting.default)
          $('#_customize-input-' + sliderID + '_control ').val(sliderSetting.default)
        })
      } else {
        const ratio = parseFloat(coloration)
        $(colorSlidersSelector).val(ratio)
      }
      reinitializeConnectedFields()
    }

    const reinitializeConnectedFields = () => {
      reloadConnectedFields()
      unbindConnectedFields()
      bindConnectedFields()
      refreshCurrentPaletteControl()
      resetSettings()
    }

    const confirmChanges = (callback) => {
      const altered = !!$('.c-color-palette .color.altered').length
      let confirmed = true

      if (altered) {
        confirmed = confirm('One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action.')
      }

      if (!altered || confirmed) {
        if (typeof callback === 'function') {
          callback()
        }
        return true
      }

      return false
    }

    const bindEvents = () => {
      const paletteControlSelector = '.c-color-palette__control'
      const $paletteControl = $(paletteControlSelector)
      const variation = getCurrentVariation()

      $paletteControl.removeClass('active')
      $paletteControl.filter('.variation-' + variation).addClass('active')

      $(document).on('click', '.js-color-palette input', function (e) {
        if (!confirmChanges(onPaletteChange.bind(this))) {
          e.preventDefault()
        }
      })

      $('[for*="sm_palette_filter"], [for*="sm_coloration_level"], [for*="sm_color_diversity"], [for*="sm_shuffle_colors"], [for*="sm_dark_mode"]').on('click', function (e) {
        if (!confirmChanges()) {
          e.preventDefault()
        }
      })

      $('[name*="sm_coloration_level"]').on('change', applyColorationValueToFields)
      $('[name*="sm_color_diversity"]').on('change', reinitializeConnectedFields)
      $('[name*="sm_shuffle_colors"]').on('change', reinitializeConnectedFields)
      $('[name*="sm_dark_mode"]').on('change', reinitializeConnectedFields)
      $('[name*="sm_palette_filter"]').on('change', () => {
        updateFilteredColors()
        reinitializeConnectedFields()
      })
    }

    const updateFilterPreviews = _.debounce(() => {
      $('.sm-palette-filter').each(function () {
        const $filters = $(this).find('input')

        $filters.each(function (i, obj) {
          const $input = $(obj)
          const $label = $input.next('label')
          const label = $input.val()
          const $colors = $label.find('.sm-color-palette__color')

          $colors.each(function (j, color) {
            const $color = $(color)
            const settingID = $color.data('setting')
            const setting = api(settingID)
            const originalColor = setting()

            $color.css('color', filterColor(originalColor, label))
          })
        })
      })
    }, 30)

    function swapValues (settingOne, settingTwo) {
      const colorPrimary = api(settingOne)()
      const colorSecondary = api(settingTwo)()

      api(settingOne).set(colorSecondary)
      api(settingTwo).set(colorPrimary)
    }

    const handleSwapValues = function () {
      const $document = $(document)

      $document.on('click', '[data-action="sm_swap_colors"]', function (e) {
        e.preventDefault()
        swapValues('sm_color_primary', 'sm_color_secondary')
      })

      $document.on('click', '[data-action="sm_swap_dark_light"]', function (e) {
        e.preventDefault()
        swapValues('sm_dark_primary', 'sm_light_primary')
        swapValues('sm_dark_secondary', 'sm_light_secondary')
        swapValues('sm_dark_tertiary', 'sm_light_tertiary')
      })

      $document.on('click', '[data-action="sm_swap_colors_dark"]', function (e) {
        e.preventDefault()
        swapValues('sm_color_primary', 'sm_dark_primary')
        swapValues('sm_color_secondary', 'sm_dark_secondary')
        swapValues('sm_color_tertiary', 'sm_dark_tertiary')
      })

      $document.on('click', '[data-action="sm_swap_secondary_colors_dark"]', function (e) {
        e.preventDefault()
        swapValues('sm_color_secondary', 'sm_dark_secondary')
      })
    }

    api.bind('ready', function () {
      // We need to do this here to be sure the data is available.
      apiSettings = api.settings.settings

      setupGlobals()

      createCurrentPaletteControls()

      reloadConnectedFields()
      updateFilteredColors()
      unbindConnectedFields()
      bindConnectedFields()
      refreshCurrentPaletteControl()

      updateFilterPreviews()

      handleSwapValues()

      bindEvents()
    })

    return {}
  }() )
})(jQuery, customify, wp)
