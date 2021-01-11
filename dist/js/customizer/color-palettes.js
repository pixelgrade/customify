/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = lodash;

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(0);
var external_lodash_default = /*#__PURE__*/__webpack_require__.n(external_lodash_);

// EXTERNAL MODULE: external "jQuery"
var external_jQuery_ = __webpack_require__(1);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);

// CONCATENATED MODULE: ./src/js/customizer/color-palettes/utils/index.js



// return an array with the hex values of the current palette
const getCurrentPaletteColors = () => {
  const colors = []
  external_lodash_default.a.each( customify.colorPalettes.masterSettingIds, function( settingID ) {
    const setting = wp.customize( settingID )
    const color = setting()
    colors.push( color )
  } )
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

const hexDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f']

function hex (x) {
  return isNaN(x) ? '00' : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16]
}

function rgb2hex (color) {
  return '#' + hex(color[0]) + hex(color[1]) + hex(color[2])
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


const filterColor = (color, filter) => {
  filter = typeof filter === 'undefined' ? external_jQuery_default()('[name*="sm_palette_filter"]:checked').val() : filter

  let newColor = hex2rgba(color)
  const palette = getCurrentPaletteColors()

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
    newColor.saturation = mix('saturation', newColor, hex2rgba('#FFF'), 0.3)
    newColor.lightness = mix('lightness', newColor, hex2rgba('#FFF'), 0.1)
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

// CONCATENATED MODULE: ./src/js/customizer/color-palettes/index.js





/** @namespace customify */
window.customify = window.customify || parent.customify || {};

const newMasterIDs = [
  'sm_accent_color_switch_master',
  'sm_accent_color_select_master',
  'sm_text_color_switch_master',
  'sm_text_color_select_master',
];

const switchColorSelector = '#_customize-input-sm_dark_color_switch_slider_control'
const selectColorSelector = '#_customize-input-sm_dark_color_select_slider_control'
const primaryColorSelector = '#_customize-input-sm_dark_color_primary_slider_control'
const secondaryColorSelector = '#_customize-input-sm_dark_color_secondary_slider_control'
const tertiaryColorSelector = '#_customize-input-sm_dark_color_tertiary_slider_control'

const defaultVariation = 'light'

/**
 * Expose the API publicly on window.customify.colorPalettes
 *
 * @namespace customify.colorPalettes
 */
if ( typeof customify.colorPalettes === 'undefined' ) {
  customify.colorPalettes = {}
}
external_lodash_default.a.extend( customify.colorPalettes, function () {
  const api = wp.customize
  let apiSettings


  const filteredColors = {}


  const colorSlidersSelector = [
    switchColorSelector,
    selectColorSelector,
    primaryColorSelector,
    secondaryColorSelector,
    tertiaryColorSelector
  ].join( ', ' );

  let setupGlobalsDone = false

  const setupGlobals = () => {

    if (setupGlobalsDone) {
      return
    }

    // Initialize filtered colors global.
    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (settingID) {
      filteredColors[settingID] = ''
    })

    // Cache initial settings configuration to be able to update connected fields on variation change.
    if (typeof customify.settingsClone === 'undefined') {
      customify.settingsClone = external_jQuery_default.a.extend(true, {}, apiSettings)
    }

    // Create a stack of callbacks bound to parent settings to be able to unbind them
    // when altering the connected_fields attribute.
    if (typeof customify.colorPalettes.connectedFieldsCallbacks === 'undefined') {
      customify.colorPalettes.connectedFieldsCallbacks = {}
    }

    setupGlobalsDone = true
  }

  const resetSettings = () => {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    external_lodash_default.a.each(settingIDs, function (settingID) {
      const setting = api(settingID)

      if (typeof setting !== 'undefined') {
        let parentSettingData = apiSettings[settingID]

        const finalValue = external_lodash_default.a.includes(newMasterIDs,settingID) ? setting() : getFilteredColor( settingID )

        external_lodash_default.a.each(parentSettingData.connected_fields, function (connectedFieldData) {
          if (external_lodash_default.a.isUndefined(connectedFieldData) || external_lodash_default.a.isUndefined(connectedFieldData.setting_id) || !external_lodash_default.a.isString(connectedFieldData.setting_id)) {
            return
          }
          const connectedSetting = api(connectedFieldData.setting_id)
          if (external_lodash_default.a.isUndefined(connectedSetting)) {
            return
          }

          connectedSetting.set(finalValue)
        })

        // Also set the final setting value, for safe keeping.
        const finalSettingID = settingID + '_final'
        const finalSetting = api(finalSettingID)
        if (!external_lodash_default.a.isUndefined(finalSetting)) {
          if (!external_lodash_default.a.isUndefined(parentSettingData.connected_fields) && !external_lodash_default.a.isEmpty(parentSettingData.connected_fields)) {
            finalSetting.set(finalValue)
          } else {
            finalSetting.set('')
          }
        }
      }
    })
  }

  const updateFilteredColors = () => {
    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (settingID) {
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
      const finalValue = external_lodash_default.a.includes(newMasterIDs,parentSettingID) ? newValue : getFilteredColor(parentSettingID)

      external_lodash_default.a.each(parentSettingData.connected_fields, function (connectedFieldData) {
        if (external_lodash_default.a.isUndefined(connectedFieldData) || external_lodash_default.a.isUndefined(connectedFieldData.setting_id) || !external_lodash_default.a.isString(connectedFieldData.setting_id)) {
          return
        }
        const setting = api(connectedFieldData.setting_id)
        if (external_lodash_default.a.isUndefined(setting)) {
          return
        }

        setting.set(finalValue)
      })
      updateFilterPreviews()

      // Also set the final setting value, for safe keeping.
      const finalSettingID = parentSettingID + '_final'
      const finalSetting = api(finalSettingID)
      if (!external_lodash_default.a.isUndefined(finalSetting)) {
        if (!external_lodash_default.a.isUndefined(parentSettingData.connected_fields) && !external_lodash_default.a.isEmpty(parentSettingData.connected_fields)) {
          finalSetting.set(finalValue)
        } else {
          finalSetting.set('')
        }
      }
    }
  }

  const bindConnectedFields = function () {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    external_lodash_default.a.each(settingIDs, function (parentSettingID) {
      if (typeof apiSettings[parentSettingID] !== 'undefined') {
        const parentSettingData = apiSettings[parentSettingID]
        const parentSetting = api(parentSettingID)

        if (!external_lodash_default.a.isUndefined(parentSettingData.connected_fields)) {
          customify.colorPalettes.connectedFieldsCallbacks[parentSettingID] = getMasterFieldCallback(parentSettingData, parentSettingID)
          parentSetting.bind(customify.colorPalettes.connectedFieldsCallbacks[parentSettingID])
          external_lodash_default.a.each(parentSettingData.connected_fields, function (connectedFieldData) {
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
    external_lodash_default.a.each(customify.colorPalettes.connectedFieldsCallbacks, function (callback, settingID) {
      const setting = api(settingID)
      setting.unbind(callback)
    })
    customify.colorPalettes.connectedFieldsCallbacks = {}
  }

  // alter connected fields of the master colors controls depending on the selected palette variation
  const getCurrentVariation = () => {
    const setting = api('sm_color_palette_variation')

    if (external_lodash_default.a.isUndefined(setting)) {
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





  const createCurrentPaletteControls = () => {
    const $palette = external_jQuery_default()('.c-color-palette')
    const $fields = $palette.find('.c-color-palette__fields').find('input')

    if (!$palette.length) {
      return
    }

    const $colors = $palette.find('.sm-color-palette__color')

    $colors.each((i, obj) => {
      const $obj = external_jQuery_default()(obj)
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
          external_jQuery_default()(obj).data('target').not($input).hide()
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
          external_jQuery_default()(other).data('target').iris('hide')
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

    external_jQuery_default()('body').on('click', function () {
      $colors.removeClass('active inactive')
      $colors.each(function (i, obj) {
        const $input = external_jQuery_default()(obj).data('target')

        if (!external_jQuery_default()(obj).hasClass('js-no-picker')) {
          $input.iris('hide')
        }
        $input.hide()
      })
    })
  }

  const showNewColors = function () {
    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (id) {
      external_jQuery_default()('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', getFilteredColor(id))
    })
  }

  const showOldColors = function () {
    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (id) {
      const setting = api(id)
      const initialColor = setting()
      external_jQuery_default()('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', initialColor)
    })
  }

  const onPaletteChange = function () {
    external_jQuery_default()(this).trigger('customify:preset-change')
    updateFilteredColors()
    reinitializeConnectedFields()
  }

  // this function goes through all the connected fields and adds swatches to the default color picker for all the colors in the current color palette
  const setPalettesOnConnectedFields = external_lodash_default.a.debounce(() => {
    let $targets = external_jQuery_default()()
    // loop through the master settings
    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (parentSettingID) {
      if (typeof apiSettings[parentSettingID] !== 'undefined') {
        const parentSettingData = apiSettings[parentSettingID]

        if (!external_lodash_default.a.isUndefined(parentSettingData.connected_fields)) {
          // loop through all the connected fields and search the element on which the iris plugin has been initialized
          external_lodash_default.a.each(parentSettingData.connected_fields, function (connectedFieldData) {
            // the connected_setting_id is different than the actual id attribute of the element we're searching for
            // so we have to do some regular expressions
            const connectedSettingID = connectedFieldData.setting_id
            const matches = connectedSettingID.match(/\[(.*?)\]/)

            if (matches) {
              const targetID = matches[1]
              const $target = external_jQuery_default()('.customize-control-color').filter('[id*="' + targetID + '"]').find('.wp-color-picker')
              $targets = $targets.add($target)
            }
          })
        }
      }
    })
    // apply the current color palettes to all the elements found
    $targets.iris({palettes: getCurrentPaletteColors()})
  }, 30)

  const toggleAlteredClassOnMasterControls = external_lodash_default.a.debounce(() => {
    const alteredSettings = []
    let alteredSettingsSelector

    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
      let connectedFields = apiSettings[masterSettingId]['connected_fields']
      const masterSettingValue = api(masterSettingId)()
      let connectedFieldsWereAltered = false

      if (!external_lodash_default.a.isUndefined(connectedFields) && !Array.isArray(connectedFields)) {
        connectedFields = Object.keys(connectedFields).map(function (key) {
          return connectedFields[key]
        })
      }

      if (!external_lodash_default.a.isUndefined(connectedFields) && connectedFields.length) {
        external_lodash_default.a.each(connectedFields, function (connectedField) {
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

    external_jQuery_default()('.c-color-palette .color').removeClass('altered')

    if (alteredSettings.length) {
      external_jQuery_default()('.c-color-palette .color').filter(alteredSettingsSelector).addClass('altered')
    }

  }, 30)

  const toggleHiddenClassOnMasterControls = external_lodash_default.a.debounce(() => {
    const optionsToShow = []
    let optionsSelector

    external_lodash_default.a.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
      const connectedFields = apiSettings[masterSettingId]['connected_fields']

      if (!external_lodash_default.a.isUndefined(connectedFields) && !external_lodash_default.a.isEmpty(connectedFields)) {
        optionsToShow.push(masterSettingId)
      }
    })

    if (!external_lodash_default.a.isEmpty(optionsToShow)) {
      optionsSelector = '.' + optionsToShow.join(', .')
    } else {
      optionsSelector = '*'
    }

    external_jQuery_default()('.sm-palette-filter .color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
    external_jQuery_default()('.sm-color-palette__color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
    external_jQuery_default()('.js-color-palette .palette__item').addClass('hidden').filter(optionsSelector).removeClass('hidden')
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

    external_lodash_default.a.each(swapMap, function (fromArray, to) {
      if (typeof newSettings[to] !== 'undefined') {
        let newConnectedFields = []
        if (fromArray instanceof Array) {
          external_lodash_default.a.each(fromArray, function (from) {
            let oldConnectedFields
            if (external_lodash_default.a.isUndefined(oldSettings[from]['connected_fields'])) {
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
    return external_lodash_default.a.clone(newSettings)
  }

  const moveConnectedFields = (oldSettings, from, to, ratio) => {

    const settings = external_lodash_default.a.clone(oldSettings)

    if (!external_lodash_default.a.isUndefined(settings[to]) && !external_lodash_default.a.isUndefined(settings[from])) {

      if (external_lodash_default.a.isUndefined(settings[from]['connected_fields'])) {
        settings[from]['connected_fields'] = []
      }

      if (external_lodash_default.a.isUndefined(settings[to]['connected_fields'])) {
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
    const diversityOptions = external_jQuery_default()('[name*="sm_color_diversity"]')
    const colorationOptions = external_jQuery_default()('[name*="sm_coloration_level"]')

    const hasDiversityOption = !!diversityOptions.length
    const isDefaultDiversitySelected = typeof diversityOptions.filter(':checked').data('default') !== 'undefined'
    const isDefaultDiversity = hasDiversityOption ? isDefaultDiversitySelected : true

    const hasColorationOptions = !!colorationOptions.length
    const isDefaultColorationSelected = typeof external_jQuery_default()('[name*="sm_coloration_level"]:checked').data('default') !== 'undefined'
    const isDefaultColoration = hasColorationOptions ? isDefaultColorationSelected : true

    const selectedDiversity = hasDiversityOption ? external_jQuery_default()('[name*="sm_color_diversity"]:checked').val() : api('sm_color_diversity')()

    if (!isDefaultDiversity || !isDefaultColoration) {
      const switchRatio = external_jQuery_default()( switchColorSelector ).val() / 100;
      const selectRatio = external_jQuery_default()( selectColorSelector ).val() / 100;
      const primaryRatio = external_jQuery_default()( primaryColorSelector ).val() / 100;
      const secondaryRatio = external_jQuery_default()( secondaryColorSelector ).val() / 100;
      const tertiaryRatio = external_jQuery_default()( tertiaryColorSelector ).val() / 100;

      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio)

      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio)

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

    const shuffle = external_jQuery_default()('[name*="sm_shuffle_colors"]:checked').val()
    if (shuffle !== 'default') {
      const shuffleVariation = getSwapMap('shuffle_' + shuffle)
      tempSettings = swapConnectedFields(tempSettings, shuffleVariation)
    }

    const dark_mode = external_jQuery_default()('[name*="sm_dark_mode_control"]:checked').val()
    if (dark_mode === 'on') {
      const dark_mmode_variation = getSwapMap('dark')
      tempSettings = swapConnectedFields(tempSettings, dark_mmode_variation)
    }

    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    external_lodash_default.a.each(settingIDs, function (masterSettingId) {
      apiSettings[masterSettingId] = tempSettings[masterSettingId]
    })
  }

  const applyColorationValueToFields = () => {
    const coloration = external_jQuery_default()('[name*="sm_coloration_level"]:checked').val()

    if (typeof external_jQuery_default()('[name*="sm_coloration_level"]:checked').data('default') !== 'undefined') {

      const sliders = ['sm_dark_color_primary_slider', 'sm_dark_color_secondary_slider', 'sm_dark_color_tertiary_slider']
      external_lodash_default.a.each(sliders, function (sliderID) {
        const sliderSetting = customify.config.settings[sliderID]
        api(sliderID).set(sliderSetting.default)
        external_jQuery_default()('#_customize-input-' + sliderID + '_control ').val(sliderSetting.default)
      })
    } else {
      const ratio = parseFloat(coloration)
      external_jQuery_default()(colorSlidersSelector).val(ratio)
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
    const altered = !!external_jQuery_default()('.c-color-palette .color.altered').length
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
    const $paletteControl = external_jQuery_default()(paletteControlSelector)
    const variation = getCurrentVariation()

    $paletteControl.removeClass('active')
    $paletteControl.filter('.variation-' + variation).addClass('active')

    external_jQuery_default()(document).on('click', '.js-color-palette input', function (e) {
      if (!confirmChanges(onPaletteChange.bind(this))) {
        e.preventDefault()
      }
    })

    external_jQuery_default()('[for*="sm_palette_filter"], [for*="sm_coloration_level"], [for*="sm_color_diversity"], [for*="sm_shuffle_colors"], [for*="sm_dark_mode"]').on('click', function (e) {
      if (!confirmChanges()) {
        e.preventDefault()
      }
    })

    external_jQuery_default()( colorSlidersSelector ).on( 'change', reinitializeConnectedFields );

    external_jQuery_default()('[name*="sm_coloration_level"]').on('change', applyColorationValueToFields)
    external_jQuery_default()('[name*="sm_color_diversity"]').on('change', reinitializeConnectedFields)
    external_jQuery_default()('[name*="sm_shuffle_colors"]').on('change', reinitializeConnectedFields)
    external_jQuery_default()('[name*="sm_dark_mode"]').on('change', reinitializeConnectedFields)
    external_jQuery_default()('[name*="sm_palette_filter"]').on('change', () => {
      updateFilteredColors()
      reinitializeConnectedFields()
    })
  }

  const updateFilterPreviews = external_lodash_default.a.debounce(() => {
    external_jQuery_default()('.sm-palette-filter').each(function () {
      const $filters = external_jQuery_default()(this).find('input')

      $filters.each(function (i, obj) {
        const $input = external_jQuery_default()(obj)
        const $label = $input.next('label')
        const label = $input.val()
        const $colors = $label.find('.sm-color-palette__color')

        $colors.each(function (j, color) {
          const $color = external_jQuery_default()(color)
          const settingID = $color.data('setting')
          const setting = api(settingID)
          const originalColor = setting()

          $color.css('color', filterColor(originalColor, label))
        })
      })
    })
  }, 30)

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

    bindEvents()
  })

  return {}
}() )


/***/ })
/******/ ]);