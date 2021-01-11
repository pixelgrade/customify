import $ from 'jquery';
import _ from "lodash";

// return an array with the hex values of the current palette
export const getCurrentPaletteColors = () => {
  const colors = []
  _.each( customify.colorPalettes.masterSettingIds, function( settingID ) {
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


export const filterColor = (color, filter) => {
  filter = typeof filter === 'undefined' ? $('[name*="sm_palette_filter"]:checked').val() : filter

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
