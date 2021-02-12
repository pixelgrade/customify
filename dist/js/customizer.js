(this.sm = this.sm || {}).customizer =
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 815:
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "getFontDetails": function() { return /* reexport */ getFontDetails; }
});

// EXTERNAL MODULE: external "jQuery"
var external_jQuery_ = __webpack_require__(609);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);
// EXTERNAL MODULE: ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js
var injectStylesIntoStyleTag = __webpack_require__(379);
var injectStylesIntoStyleTag_default = /*#__PURE__*/__webpack_require__.n(injectStylesIntoStyleTag);
// EXTERNAL MODULE: ./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/js/customizer/colors/color-palette-builder/components/builder/components/color-controls/style.scss
var style = __webpack_require__(451);
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/components/builder/components/color-controls/style.scss

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = injectStylesIntoStyleTag_default()(style/* default */.Z, options);



/* harmony default export */ var color_controls_style = (style/* default.locals */.Z.locals || {});
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/components/builder/components/color-controls/index.js
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



var ColorControls = function ColorControls(props) {
  var colors = props.colors,
      setColors = props.setColors;
  return /*#__PURE__*/React.createElement("div", {
    className: "c-palette-builder"
  }, /*#__PURE__*/React.createElement("div", {
    className: "c-palette-builder__source-list"
  }, colors.map(function (color, index) {
    return /*#__PURE__*/React.createElement("div", {
      className: "c-palette-builder__source-item"
    }, /*#__PURE__*/React.createElement("input", {
      className: "c-palette-builder__source-item-label",
      type: "text",
      value: color.label,
      onChange: function onChange(e) {
        var newColors = colors.slice();
        newColors[index] = {
          label: e.target.value,
          value: color.value
        };
        setColors(newColors);
      }
    }), /*#__PURE__*/React.createElement("input", {
      className: "c-palette-builder__source-item-color",
      type: "color",
      value: color.value,
      onChange: function onChange(e) {
        var newColors = colors.slice();
        newColors[index] = {
          label: color.label,
          value: e.target.value
        };
        setColors(newColors);
      }
    }), /*#__PURE__*/React.createElement("button", {
      className: "c-palette-builder__source-item-delete button",
      onClick: function onClick(e) {
        e.preventDefault();
        var newColors = colors.slice();
        newColors.splice(index, 1);
        setColors(newColors);
      }
    }, "Delete"));
  })), /*#__PURE__*/React.createElement("button", {
    className: "c-palette-builder__add button",
    onClick: function onClick(e) {
      e.preventDefault();
      setColors([].concat(_toConsumableArray(colors), [{
        label: 'Dark',
        value: '#111111'
      }]));
    }
  }, "Add Color"));
};

/* harmony default export */ var color_controls = (ColorControls);
// EXTERNAL MODULE: ./node_modules/hsluv/hsluv.js
var hsluv = __webpack_require__(119);
// EXTERNAL MODULE: ./node_modules/chroma-js/chroma.js
var chroma_js_chroma = __webpack_require__(792);
var chroma_default = /*#__PURE__*/__webpack_require__.n(chroma_js_chroma);
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/components/builder/contrast-array.js
var optimalContrastArray = Array.from(Array(12)).map(function (x, i) {
  return Math.pow(21, i / 11);
}); //	https://medium.com/envoy-design/designing-an-accessible-color-scheme-again-fd35cfa9d796

var contrastRangesArray = [[1, 1], [1.07, 1.17], [1.21, 1.31], [1.5, 1.91], [2.1, 2.63], [3, 3.5], [4.51, 4.67], [6, 7], [8.75, 10.5], [11.67, 15], [16.15, 19.1], [21, 21]]; // powers of 21 ^ 1/10 but with small adjustments for the lighter colors

var myOptimalContrastArray = [1, 1.07, // 1.32
1.25, // 1.74
1.8, // 2.29
2.63, // 3.03
3.99, 5.26, 6.94, 9.15, 12.07, 15.92, 19 // almost black (21)
];
var minContrastArray = contrastRangesArray.map(function (x) {
  return x[0];
});
var maxContrastArray = contrastRangesArray.map(function (x) {
  return x[1];
});
/* harmony default export */ var contrast_array = (myOptimalContrastArray);
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/components/builder/utils.js
function utils_toConsumableArray(arr) { return utils_arrayWithoutHoles(arr) || utils_iterableToArray(arr) || utils_unsupportedIterableToArray(arr) || utils_nonIterableSpread(); }

function utils_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function utils_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return utils_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return utils_arrayLikeToArray(o, minLen); }

function utils_iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function utils_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return utils_arrayLikeToArray(arr); }

function utils_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }




var getPalettesFromColors = function getPalettesFromColors(colors) {
  var attributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var functionalColors = getFunctionalColors(colors);
  var palettes = colors.map(mapColorToPalette(attributes));
  var functionalPalettes = functionalColors.map(mapColorToPalette(attributes));
  palettes = addAutoPalettes(palettes, attributes);
  return mapSanitizePalettes(palettes.concat(functionalPalettes), attributes);
};
var addAutoPalettes = function addAutoPalettes(palettes, attributes) {
  if (!attributes.colorInterpolation) {
    return palettes;
  }

  var newPalettes = JSON.parse(JSON.stringify(palettes));

  if (newPalettes.length > 1) {
    var index0 = getBestPositionInPaletteByLuminance(newPalettes[0].source, newPalettes[0].colors, attributes);
    var index1 = getBestPositionInPaletteByLuminance(newPalettes[1].source, newPalettes[1].colors, attributes);
    var distance0 = Math.abs(index0 - index1);
    var distance1 = 0;
    var distance2 = 0;

    if (newPalettes.length > 2) {
      var index2 = getBestPositionInPaletteByLuminance(newPalettes[2].source, newPalettes[2].colors, attributes);
      distance1 = Math.abs(index1 - index2);
      distance2 = Math.abs(index0 - index2);
      var distance = Math.min(distance0, distance1, distance2);

      if (distance > 2) {
        var newPalette = createAutoPalette(newPalettes.slice(0, 3), attributes);
        newPalettes.splice(0, 3, newPalette);
        return newPalettes;
      }
    }

    if (distance0 > 2) {
      var _newPalette = createAutoPalette([newPalettes[0], newPalettes[1]], attributes);

      newPalettes.splice(0, 2, _newPalette);
      return newPalettes;
    }

    if (distance2 > 2) {
      var _newPalette2 = createAutoPalette([newPalettes[0], newPalettes[2]], attributes);

      newPalettes.splice(0, 3, _newPalette2, newPalettes[1]);
      return newPalettes;
    }

    if (distance1 > 2) {
      var _newPalette3 = createAutoPalette([newPalettes[1], newPalettes[2]], attributes);

      newPalettes.splice(0, 3, newPalettes[0], _newPalette3);
      return newPalettes;
    }
  }

  return newPalettes;
};
var mapSanitizePalettes = function mapSanitizePalettes(colors) {
  var attributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  return colors.map(mapCorrectLightness(attributes)).map(mapUpdateProps).map(mapUseSource(attributes)).map(mapAddSourceIndex(attributes)).map(mapAddTextColors);
};
var getFunctionalColors = function getFunctionalColors(colors) {
  if (!colors || !colors.length) {
    return [];
  }

  var color = colors[0].value;
  var red = chroma_default()(color).set('hsl.h', 0).hex();
  var blue = chroma_default()(color).set('hsl.h', 180).hex();
  var yellow = chroma_default()(color).set('hsl.h', 60).hex();
  var green = chroma_default()(color).set('hsl.h', 120).hex();
  return [{
    label: '_info',
    value: blue,
    id: 'info'
  }, {
    label: '_error',
    value: red,
    id: 'error'
  }, {
    label: '_warning',
    value: yellow,
    id: 'warning'
  }, {
    label: '_success',
    value: green,
    id: 'success'
  }];
};
var getSourceIndex = function getSourceIndex(palette) {
  return palette.colors.findIndex(function (color) {
    return color.value === palette.source;
  });
};
var mapAddTextColors = function mapAddTextColors(palette) {
  palette.textColors = palette.colors.slice(9, 11).map(function (color, index) {
    return _objectSpread({
      value: getTextColor(palette.source, index)
    }, color);
  });
  return palette;
};
var mapAddSourceIndex = function mapAddSourceIndex(attributes) {
  return function (palette, index, palettes) {
    var source = palette.source,
        colors = palette.colors;
    var sourceIndex = getSourceIndex(palette); // falback sourceIndex when the source isn't used in the palette

    if (!sourceIndex > -1) {
      sourceIndex = getBestPositionInPaletteByLuminance(source, colors.map(function (color) {
        return color.value;
      }), attributes);
    }

    return _objectSpread({
      sourceIndex: sourceIndex
    }, palette);
  };
};
var getShiftedArray = function getShiftedArray(array, positions) {
  var arrayClone = array.slice();
  var chunk = arrayClone.splice(0, positions);
  arrayClone.push.apply(arrayClone, utils_toConsumableArray(chunk));
  return arrayClone;
};
var mapShiftColors = function mapShiftColors(palette) {
  palette.colors = getShiftedArray(palette.colors);
  return palette;
};
var mapColorToPalette = function mapColorToPalette(attributes) {
  var mode = attributes.mode;
  return function (colorObj, index) {
    var label = colorObj.label,
        id = colorObj.id,
        value = colorObj.value;
    var colors = contrast_array.map(function (contrast) {
      var luminance = contrastToLuminance(contrast);
      return chroma_default()(value).luminance(luminance, mode).hex();
    });
    return {
      id: id || index + 1,
      label: label,
      source: value,
      colors: colors
    };
  };
};
var mapInterpolateSource = function mapInterpolateSource(attributes) {
  var mode = attributes.mode;
  return function (palette) {
    var source = palette.source;
    var position = getBestPositionInPaletteByLuminance(source, palette.colors, attributes);

    if (mode !== 'none') {
      var stops = [{
        value: '#FFFFFF',
        position: 0
      }, {
        value: source,
        position: position
      }, {
        value: '#000000',
        position: 11
      }];
      palette.colors = getColorsFromStops(stops, attributes);
    }

    return palette;
  };
};

var getColorsFromStops = function getColorsFromStops(stops, attributes) {
  var mode = attributes.mode;
  var colors = [stops[0].value];

  for (var i = 0; i < stops.length - 1; i++) {
    var scale = chroma.scale([stops[i].value, stops[i + 1].value]).mode(mode);
    var scaleColors = scale.colors(stops[i + 1].position - stops[i].position + 1);
    colors.push.apply(colors, utils_toConsumableArray(scaleColors.slice(1)));
  }

  return colors;
};

var mapCorrectLightness = function mapCorrectLightness(_ref) {
  var correctLightness = _ref.correctLightness,
      mode = _ref.mode;

  if (!correctLightness) {
    return noop;
  }

  return function (palette) {
    palette.colors = palette.colors.map(function (color, index) {
      var luminance = contrastToLuminance(contrast_array[index]);
      return chroma_default()(color).luminance(luminance, 'rgb').hex();
    });
    return palette;
  };
};

var mapUpdateProps = function mapUpdateProps(palette) {
  palette.colors = palette.colors.map(function (color, index) {
    return Object.assign({}, {
      value: color
    });
  });
  return palette;
};

var mapUseSource = function mapUseSource(attributes) {
  var useSources = attributes.useSources,
      colorInterpolation = attributes.colorInterpolation,
      bezierInterpolation = attributes.bezierInterpolation;

  if (!useSources) {
    return noop;
  }

  return function (palette) {
    var source = palette.source;
    var position = getBestPositionInPaletteByLuminance(source, palette.colors.map(function (color) {
      return color.value;
    }), attributes);
    palette.colors.splice(position, 1, {
      value: source,
      isSource: true
    });
    return palette;
  };
};
var getBestPositionInPaletteByLuminance = function getBestPositionInPaletteByLuminance(color, colors, attributes, byColorDistance) {
  var min = Number.MAX_SAFE_INTEGER;
  var pos = -1;

  for (var i = 0; i < colors.length - 1; i++) {
    var distance = void 0;

    if (!!byColorDistance) {
      distance = chroma_default().distance(colors[i], color, 'rgb');
    } else {
      distance = Math.abs(chroma_default()(colors[i]).luminance() - chroma_default()(color).luminance());
    }

    if (distance < min) {
      min = distance;
      pos = i;
    }
  }

  var firstDarkPos = Math.ceil(colors.length / 2); // if we want to preserve contrast we should do this

  if (attributes !== null && attributes !== void 0 && attributes.correctLightness) {
    if (chroma_default().contrast(color, 'white') > Math.sqrt(21)) {
      pos = Math.max(firstDarkPos, pos);
    } else {
      pos = Math.min(firstDarkPos - 1, pos);
    }
  }

  return pos;
};

var getTextColor = function getTextColor(source, position, mode) {
  var luminance = contrastToLuminance(contrast_array[position]);
  var hpluv = (0,hsluv.hexToHpluv)(source);
  var h = Math.min(Math.max(hpluv[0], 0), 360);
  var p = Math.min(Math.max(hpluv[1], 0), 100);
  var l = Math.min(Math.max(hpluv[2], 0), 100);
  var rgb = (0,hsluv.hpluvToRgb)([h, p, l]).map(function (x) {
    return Math.max(0, Math.min(x * 255, 255));
  });
  return chroma_default()(rgb).luminance(luminance, mode).hex();
};

var contrastToLuminance = function contrastToLuminance(contrast) {
  return 1.05 / contrast - 0.05;
};

var getVariablesCSS = function getVariablesCSS(palette) {
  var offset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var isDark = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
  var isShifted = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
  var colors = palette.colors;
  var count = colors.length;
  return colors.reduce(function (colorsAcc, color, index) {
    var oldColorIndex = (index + offset) % count;

    if (isDark) {
      if (oldColorIndex < count / 2) {
        oldColorIndex = 11 - oldColorIndex;
      } else {
        return colorsAcc;
      }
    }

    return "".concat(colorsAcc, "\n      ").concat(getColorVariables(palette, "".concat(index), oldColorIndex, isShifted), "\n    ");
  }, '');
};
var getInitialColorVaraibles = function getInitialColorVaraibles(palette) {
  var colors = palette.colors,
      textColors = palette.textColors,
      id = palette.id;
  var prefix = '--sm-color-palette-';
  var accentColors = colors.reduce(function (colorsAcc, color, index) {
    return "".concat(colorsAcc, "\n      ").concat(prefix).concat(id, "-color-").concat(index + 1, ": ").concat(color.value, ";\n    ");
  }, '');
  var darkColors = textColors.reduce(function (colorsAcc, color, index) {
    return "".concat(colorsAcc, "\n      ").concat(prefix).concat(id, "-text-color-").concat(index + 1, ": ").concat(color.value, ";\n    ");
  }, '');
  return "\n    ".concat(accentColors, "\n    ").concat(darkColors, "\n  ");
};
var getColorVariables = function getColorVariables(palette, newColorIndex, oldColorIndex, isShifted) {
  var colors = palette.colors,
      id = palette.id;
  var count = colors.length;
  var accentColorIndex = (oldColorIndex + count / 2) % count;
  var prefix = '--sm-color-palette-';
  var suffix = isShifted ? '-shifted' : '';
  var newIndex = parseInt(newColorIndex, 10) + 1;
  var accentColors = "\n    ".concat(prefix).concat(id, "-bg-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-color-").concat(oldColorIndex + 1, ");\n    ").concat(prefix).concat(id, "-accent-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-color-").concat(accentColorIndex + 1, ");\n  ");
  var darkColors = '';

  if (oldColorIndex < count / 2) {
    darkColors = "\n      ".concat(prefix).concat(id, "-fg1-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-text-color-1);\n      ").concat(prefix).concat(id, "-fg2-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-text-color-2);\n    ");
  } else {
    darkColors = "\n      ".concat(prefix).concat(id, "-fg1-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-color-1);\n      ").concat(prefix).concat(id, "-fg2-color-").concat(newIndex).concat(suffix, ": var(").concat(prefix).concat(id, "-color-1);\n    ");
  }

  return "\n    ".concat(accentColors, "\n    ").concat(darkColors, "\n  ");
};
var getCSSFromPalettes = function getCSSFromPalettes(palettes) {
  if (!palettes.length) {
    return '';
  } // the old implementation generates 3 fallback palettes and
  // we need to overwrite all 3 of them when the user starts building a new palette
  // @todo this is necessary only in the Customizer preview


  while (palettes.length < 3) {
    palettes.push(palettes[0]);
  }

  var variationSetting = wp.customize('sm_site_color_variation');
  var variation = !!variationSetting ? parseInt(variationSetting(), 10) : 1;
  return palettes.reduce(function (palettesAcc, palette, paletteIndex, palettes) {
    var id = palette.id,
        sourceIndex = palette.sourceIndex;
    return "\n      ".concat(palettesAcc, "\n      \n      html {\n        ").concat(getInitialColorVaraibles(palette), "\n        ").concat(getVariablesCSS(palette, variation - 1), "\n        ").concat(getVariablesCSS(palette, sourceIndex, false, true), "\n      } \n      \n      .is-dark {\n        ").concat(getVariablesCSS(palette, variation - 1, true), "\n        ").concat(getVariablesCSS(palette, sourceIndex, true, true), "\n      }\n    ");
  }, '');
};
var getColorsFromInputValue = function getColorsFromInputValue(value) {
  var colors;

  try {
    colors = JSON.parse(value);
  } catch (e) {
    colors = [];
  }

  return colors;
};
var getValueFromColors = function getValueFromColors(colors) {
  return JSON.stringify(colors);
};

var createAutoPalette = function createAutoPalette(palettes) {
  var attributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var mode = attributes.mode,
      bezierInterpolation = attributes.bezierInterpolation;
  var palettesCopy = palettes.slice();
  var colors = palettesCopy.map(function (palette) {
    return palette.source;
  });
  var autoPalette = colors.slice();
  autoPalette.splice(0, 0, '#FFFFFF');
  autoPalette.push('#000000');
  autoPalette.sort(function (c1, c2) {
    return chroma_default()(c1).luminance() > chroma_default()(c2).luminance() ? -1 : 1;
  });

  if (!!bezierInterpolation) {
    autoPalette = chroma_default().bezier(autoPalette).scale().mode(mode).correctLightness().colors(12);
  } else {
    autoPalette = chroma_default().scale(autoPalette).mode(mode).correctLightness().colors(12);
  }

  autoPalette = _objectSpread(_objectSpread({}, palettesCopy[0]), {}, {
    colors: autoPalette
  });
  return autoPalette;
};

var noop = function noop(palette) {
  return palette;
};
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/components/builder/index.js
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || builder_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function builder_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return builder_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return builder_arrayLikeToArray(o, minLen); }

function builder_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





var _wp$element = wp.element,
    useEffect = _wp$element.useEffect,
    useState = _wp$element.useState;

var Builder = function Builder(props) {
  var _window, _window$myApi;

  var sourceSettingID = props.sourceSettingID,
      outputSettingID = props.outputSettingID;
  var sourceSetting = wp.customize(sourceSettingID);
  var colorSpaceSetting = wp.customize('sm_color_space');
  var colorInterpolationSetting = wp.customize('sm_color_interpolation');
  var bezierInterpolationSetting = wp.customize('sm_bezier_interpolation');
  var useSourcesSetting = wp.customize('sm_use_color_sources');
  var outputSetting = wp.customize(outputSettingID);

  var _useState = useState(getColorsFromInputValue(sourceSetting())),
      _useState2 = _slicedToArray(_useState, 2),
      colors = _useState2[0],
      setColors = _useState2[1];

  var _useState3 = useState({
    correctLightness: true,
    useSources: useSourcesSetting(),
    mode: colorSpaceSetting(),
    colorInterpolation: colorInterpolationSetting(),
    bezierInterpolation: bezierInterpolationSetting()
  }),
      _useState4 = _slicedToArray(_useState3, 2),
      attributes = _useState4[0],
      updateAttributes = _useState4[1];

  var setAttributes = function setAttributes(newAttributes) {
    updateAttributes(Object.assign({}, attributes, newAttributes));
  };

  var changeListener = function changeListener() {
    setColors(getColorsFromInputValue(sourceSetting()));
    setAttributes({
      useSources: useSourcesSetting(),
      mode: colorSpaceSetting(),
      colorInterpolation: colorInterpolationSetting(),
      bezierInterpolation: bezierInterpolationSetting()
    });
  };

  useEffect(function () {
    // Attach the listeners on component mount.
    sourceSetting.bind(changeListener);
    useSourcesSetting.bind(changeListener);
    colorSpaceSetting.bind(changeListener);
    colorInterpolationSetting.bind(changeListener);
    bezierInterpolationSetting.bind(changeListener); // Detach the listeners on component unmount.

    return function () {
      sourceSetting.unbind(changeListener);
      useSourcesSetting.unbind(changeListener);
      colorSpaceSetting.unbind(changeListener);
      colorInterpolationSetting.unbind(changeListener);
      bezierInterpolationSetting.unbind(changeListener);
    };
  }, []);
  useEffect(function () {
    sourceSetting.set(getValueFromColors(colors));
  }, [colors]);
  useEffect(function () {
    var palettes = getPalettesFromColors(colors, attributes);

    if (typeof outputSetting !== "undefined") {
      outputSetting.set(JSON.stringify(palettes));
    }
  }, [colors, attributes]);
  var palettes = getPalettesFromColors(colors, attributes);
  var isDark = (_window = window) !== null && _window !== void 0 && (_window$myApi = _window.myApi) !== null && _window$myApi !== void 0 && _window$myApi.isDark ? window.myApi.isDark() : false;
  return /*#__PURE__*/React.createElement("div", {
    className: isDark ? 'is-dark' : ''
  }, /*#__PURE__*/React.createElement(color_controls, {
    colors: colors,
    setColors: setColors
  }), /*#__PURE__*/React.createElement("style", null, getCSSFromPalettes(palettes)), palettes.map(function (palette, index) {
    var colors = palette.colors,
        id = palette.id;
    return /*#__PURE__*/React.createElement("div", {
      className: "palette-preview-set"
    }, /*#__PURE__*/React.createElement("div", {
      className: "palette-preview"
    }, colors.map(function (color, colorIndex) {
      return /*#__PURE__*/React.createElement("div", {
        className: "sm-variation-".concat(colorIndex, " "),
        style: {
          color: "var(--sm-color-palette-".concat(id, "-bg-color-").concat(colorIndex + 1, ")")
        }
      });
    })), /*#__PURE__*/React.createElement("div", {
      className: "palette-preview"
    }, colors.map(function (color, colorIndex) {
      return /*#__PURE__*/React.createElement("div", {
        className: "sm-variation-".concat(colorIndex, " "),
        style: {
          color: "var(--sm-color-palette-".concat(id, "-fg1-color-").concat(colorIndex + 1, ")")
        }
      });
    })), /*#__PURE__*/React.createElement("div", {
      className: "palette-preview"
    }, colors.map(function (color, colorIndex) {
      return /*#__PURE__*/React.createElement("div", {
        className: "sm-variation-".concat(colorIndex, " "),
        style: {
          color: "var(--sm-color-palette-".concat(id, "-accent-color-").concat(colorIndex + 1, ")")
        }
      });
    })));
  }));
};
;// CONCATENATED MODULE: ./src/js/customizer/colors/color-palette-builder/index.js

var initializePaletteBuilder = function initializePaletteBuilder(sourceSettingID, outputSettingID) {
  var containerID = "customize-control-".concat(sourceSettingID, "_control");
  var container = document.getElementById(containerID);
  var target = document.createElement('DIV');

  if (typeof container === "undefined") {
    return;
  }

  container.children.forEach(function (child) {
    child.style.display = 'none';
  });
  container.insertBefore(target, container.firstChild);
  wp.element.render( /*#__PURE__*/React.createElement(Builder, {
    sourceSettingID: sourceSettingID,
    outputSettingID: outputSettingID
  }), target);
};
;// CONCATENATED MODULE: ./src/js/customizer/colors/utils.js
var moveConnectedFields = function moveConnectedFields(oldSettings, from, to, ratio) {
  var settings = JSON.parse(JSON.stringify(oldSettings));

  if (!!settings[from] && !!settings[to]) {
    if (!settings[from].connected_fields) {
      settings[from].connected_fields = [];
    }

    if (!settings[to].connected_fields) {
      settings[to].connected_fields = [];
    }

    var oldFromConnectedFields = Object.values(settings[from]['connected_fields']);
    var oldToConnectedFields = Object.values(settings[to]['connected_fields']);
    var oldConnectedFields = oldToConnectedFields.concat(oldFromConnectedFields);
    var count = Math.round(ratio * oldConnectedFields.length);
    var newToConnectedFields = oldConnectedFields.slice(0, count);
    var newFromConnectedFields = oldConnectedFields.slice(count);
    newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
      return newToConnectedFields[key];
    });
    newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
      return newToConnectedFields[key];
    });
    settings[to]['connected_fields'] = newToConnectedFields;
    settings[from]['connected_fields'] = newFromConnectedFields;
  }

  return settings;
};
// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(804);
var external_lodash_default = /*#__PURE__*/__webpack_require__.n(external_lodash_);
;// CONCATENATED MODULE: ./src/js/customizer/global-service.js

var callbacks = {};
var settings = {};
var loadSettings = function loadSettings() {
  settings = JSON.parse(JSON.stringify(wp.customize.settings.settings));
};
var getSettings = function getSettings() {
  return settings;
};
var setSettings = function setSettings(newSettings) {
  settings = newSettings;
};
var getSetting = function getSetting(settingID) {
  return settings[settingID];
};
var setSetting = function setSetting(settingID, value) {
  settings[settingID] = value;
};
var getCallback = function getCallback(settingID) {
  return callbacks[settingID];
};
var setCallback = function setCallback(settingID, callback) {
  callbacks[settingID] = callback;
};
var getCallbacks = function getCallbacks() {
  return callbacks;
};
var deleteCallbacks = function deleteCallbacks(settingIDs) {
  settingIDs.forEach(function (settingID) {
    delete callbacks[settingID];
  });
};
var bindConnectedFields = function bindConnectedFields(settingIDs) {
  var filter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : global_service_noop;
  settingIDs.forEach(function (settingID) {
    wp.customize(settingID, function (parentSetting) {
      setCallback(settingID, function (newValue) {
        var settingConfig = getSetting(settingID);
        var connectedFields = settingConfig.connected_fields || {};
        Object.keys(connectedFields).map(function (key) {
          return connectedFields[key].setting_id;
        }).forEach(function (connectedSettingID) {
          wp.customize(connectedSettingID, function (connectedSetting) {
            connectedSetting.set(filter(newValue));
          });
        });
      });
      parentSetting.bind(getCallback(settingID));
    });
  });
};
var unbindConnectedFields = function unbindConnectedFields(settingIDs) {
  var globalCallbacks = external_lodash_default().pick(getCallbacks(), settingIDs);

  external_lodash_default().each(globalCallbacks, function (callback, settingID) {
    wp.customize(settingID, function (setting) {
      setting.unbind(callback);
    });
  });

  deleteCallbacks(settingIDs);
};

var global_service_noop = function noop(x) {
  return x;
};
;// CONCATENATED MODULE: ./src/js/customizer/colors/index.js




wp.customize.bind('ready', function () {
  initializeColors();
  reloadConnectedFields();
});

var initializeColors = function initializeColors() {
  initializePaletteBuilder('sm_advanced_palette_source', 'sm_advanced_palette_output');
  wp.customize('sm_coloration_level', function (setting) {
    setting.bind(applyColorationValueToFields);
  });
};

var reloadConnectedFields = external_lodash_default().debounce(function () {
  var settings = getSettings();
  var settingIDs = Object.keys(settings);
  unbindConnectedFields(settingIDs);
  setSettings(applyColorsConnectedFieldsAlterations(settings));
  bindConnectedFields(settingIDs);
}, 30);

var applyColorationValueToFields = function applyColorationValueToFields() {
  wp.customize('sm_coloration_level', function (colorationLevelSetting) {
    var colorationLevel = colorationLevelSetting();
    var defaultColorationLevel = getSetting('sm_coloration_level').default;
    var isDefaultColoration = colorationLevel === defaultColorationLevel;
    var darkToColorSliderControls = ['sm_dark_color_switch_slider', 'sm_dark_color_select_slider'];
    darkToColorSliderControls.forEach(function (sliderSettingID) {
      wp.customize(sliderSettingID, function (sliderSetting) {
        var defaultValue = getSetting(sliderSettingID).default;
        var value = isDefaultColoration ? defaultValue : parseFloat(colorationLevel);
        sliderSetting.set(value);
      });
    });
  });
};

var applyColorationLevel = function applyColorationLevel(tempSettings) {
  var switchSliderID = 'sm_dark_color_switch_slider';
  var selectSliderID = 'sm_dark_color_select_slider';
  wp.customize('sm_coloration_level', function (colorationLevelSetting) {
    var colorationLevel = colorationLevelSetting();
    var defaultColorationLevel = getSetting('sm_coloration_level').default;
    var isDefaultColoration = colorationLevel === defaultColorationLevel;

    if (isDefaultColoration) {
      return;
    }

    wp.customize(switchSliderID, function (switchSetting) {
      var switchRatio = switchSetting() / 100;
      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio);
    });
    wp.customize(selectSliderID, function (selectSetting) {
      var selectRatio = selectSetting() / 100;
      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio);
    });
  });
  return tempSettings;
};

var applyColorsConnectedFieldsAlterations = function applyColorsConnectedFieldsAlterations(tempSettings) {
  tempSettings = applyColorationLevel(tempSettings);
  return tempSettings;
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/callback-filter.js
var getCallbackFilter = function getCallbackFilter(connectedFieldData) {
  return function (newValue, oldValue) {
    /* ======================
     * Process the font logic to get the value that should be applied to the connected (font) fields.
     *
     * The font logic is already in the new value - @see setFieldFontsLogicConfig()
     */
    var newFontData = {};
    var fontsLogic = newValue;

    if (typeof fontsLogic.reset !== 'undefined') {
      var settingID = connectedFieldData.setting_id;
      var defaultValue = customify.config.settings[settingID].default;

      if (!_.isUndefined(setting) && !_.isEmpty(defaultValue)) {
        newFontData['font_family'] = defaultValue['font_family'];
        newFontData['font_size'] = defaultValue['font_size'];
        newFontData['line_height'] = defaultValue['line_height'];
        newFontData['letter_spacing'] = defaultValue['letter_spacing'];
        newFontData['text_transform'] = defaultValue['text_transform'];
        newFontData['font_variant'] = defaultValue['font_variant'];
      }
    }
    /* ===========
     * We need to determine the 6 subfields values to be able to determine the value of the font field.
     */
    // The font family is straight forward as it comes directly from the parent field font logic configuration.


    if (typeof fontsLogic.font_family !== 'undefined') {
      newFontData['font_family'] = fontsLogic.font_family;
    }

    if (_.isEmpty(newFontData['font_family'])) {
      // If we don't have a font family, we really can't do much.
      return;
    }

    if (typeof connectedFieldData.font_size !== 'undefined' && false !== connectedFieldData.font_size) {
      newFontData['font_size'] = sm.fontFields.standardizeNumericalValue(connectedFieldData.font_size); // Next, we what to apply the overall font size multiplier.

      if (!isNaN(newFontData['font_size'].value)) {
        // By default we use 1.
        var overallFontSizeMultiplier = 1.0;

        if (typeof fontsLogic.font_size_multiplier !== 'undefined') {
          // Make sure it is a positive float.
          overallFontSizeMultiplier = parseFloat(fontsLogic.font_size_multiplier); // We reject negative or 0 values.

          if (overallFontSizeMultiplier <= 0) {
            overallFontSizeMultiplier = 1.0;
          }
        }

        newFontData['font_size'].value = round(parseFloat(newFontData['font_size'].value) * overallFontSizeMultiplier, customify.fonts.floatPrecision);
      } // The font variant, letter spacing and text transform all come together from the font styles (intervals).
      // We just need to find the one that best matches the connected field given font size (if given).
      // Please bear in mind that we expect the font logic styles to be preprocessed, without any overlapping and using numerical keys.


      if (typeof fontsLogic.font_styles_intervals !== 'undefined' && _.isArray(fontsLogic.font_styles_intervals) && fontsLogic.font_styles_intervals.length > 0) {
        var idx = 0;

        while (idx < fontsLogic.font_styles_intervals.length - 1 && typeof fontsLogic.font_styles_intervals[idx].end !== 'undefined' && fontsLogic.font_styles_intervals[idx].end <= connectedFieldData.font_size.value) {
          idx++;
        } // We will apply what we've got.


        if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].font_variant)) {
          newFontData['font_variant'] = fontsLogic.font_styles_intervals[idx].font_variant;
        }

        if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].letter_spacing)) {
          newFontData['letter_spacing'] = sm.fontFields.standardizeNumericalValue(fontsLogic.font_styles_intervals[idx].letter_spacing);
        }

        if (!_.isEmpty(fontsLogic.font_styles_intervals[idx].text_transform)) {
          newFontData['text_transform'] = fontsLogic.font_styles_intervals[idx].text_transform;
        } // Next, we what to apply the interval font size multiplier.


        if (!isNaN(newFontData['font_size'].value)) {
          // By default we use 1.
          var fontSizeMultiplier = 1.0;

          if (typeof fontsLogic.font_styles_intervals[idx].font_size_multiplier !== 'undefined') {
            // Make sure it is a positive float.
            fontSizeMultiplier = parseFloat(fontsLogic.font_styles_intervals[idx].font_size_multiplier); // We reject negative or 0 values.

            if (fontSizeMultiplier <= 0) {
              fontSizeMultiplier = 1.0;
            }
          }

          newFontData['font_size'].value = round(parseFloat(newFontData['font_size'].value) * fontSizeMultiplier, customify.fonts.floatPrecision);
        }
      } // The line height is determined by getting the value of the polynomial function determined by points.


      if (typeof fontsLogic.font_size_to_line_height_points !== 'undefined' && _.isArray(fontsLogic.font_size_to_line_height_points)) {
        var result = regression.logarithmic(fontsLogic.font_size_to_line_height_points, {
          precision: customify.fonts.floatPrecision
        });
        var lineHeight = result.predict(newFontData['font_size'].value)[1];
        newFontData['line_height'] = sm.fontFields.standardizeNumericalValue(lineHeight);
      }
    }

    return newFontData;
  };
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/determine-font-type.js
var determineFontType = function determineFontType(fontFamily) {
  // The default is a standard font (aka no special loading or processing).
  var fontType = 'system_font'; // We will follow a stack in the following order: cloud fonts, theme fonts, Google fonts, standard fonts.

  if (typeof customify.fonts.cloud_fonts[fontFamily] !== 'undefined') {
    fontType = 'cloud_font';
  } else if (typeof customify.fonts.theme_fonts[fontFamily] !== 'undefined') {
    fontType = 'theme_font';
  } else if (typeof customify.fonts.google_fonts[fontFamily] !== 'undefined') {
    fontType = 'google_font';
  }

  return fontType;
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/get-font-details.js

var getFontDetails = function getFontDetails(fontFamily) {
  var fontType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  if (false === fontType) {
    // We will determine the font type based on font family.
    fontType = determineFontType(fontFamily);
  }

  switch (fontType) {
    case 'theme_font':
      return customify.fonts.theme_fonts[fontFamily];
      break;

    case 'cloud_font':
      return customify.fonts.cloud_fonts[fontFamily];
      break;

    case 'google_font':
      return customify.fonts.google_fonts[fontFamily];
      break;

    case 'system_font':
      if (typeof customify.fonts.system_fonts[fontFamily] !== 'undefined') {
        return customify.fonts.system_fonts[fontFamily];
      }

      break;

    default:
  }

  return false;
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/handle-font-popup-toggle.js

var handleFontPopupToggle = function handleFontPopupToggle() {
  var $allFontCheckboxes = external_jQuery_default()('.js-font-option-toggle'); // Close all other font fields popups when opening a font field popup.

  $allFontCheckboxes.on('click', function (event) {
    $allFontCheckboxes.not(event.target).prop('checked', false);
  }); // Make sure that all fonts popups are closed when backing away from a panel or section.
  // @todo This doesn't catch backing with ESC key. For that we should hook on Customizer section and panel events ('collapsed').

  external_jQuery_default()('#customize-controls .customize-panel-back, #customize-controls .customize-section-back').on('click', function () {
    $allFontCheckboxes.prop('checked', false);
  });
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/init-subfield.js


var initSubfield = function initSubfield($subField) {
  var select2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  // Mark these as not touched by the user.
  $subField.data('touched', false);
  $subField.on('input change', onSubfieldChange); // If we've been instructed, initialize a select2.

  if (true === select2) {
    $subField.select2();
  }
};

var onSubfieldChange = function onSubfieldChange(event, who) {
  var $subField = external_jQuery_default()(event.target); // The change was triggered programmatically by Customify.
  // No need to self-update the value.

  if ('customify' === who) {
    return;
  }

  var wrapper = getWrapper($subField);
  var settingID = getSettingID($subField); // Mark this input as touched by the user.

  $subField.data('touched', true); // Gather subfield values and trigger refresh of the fonts in the preview window.

  selfUpdateValue(wrapper, settingID);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/load-font-value.js


/**
 * This function is a reverse of selfUpdateValue(), initializing the entire font field controls
 * based on the setting value.
 */

var loadFontValue = function loadFontValue(wrapper, value, settingID) {
  // If we are already loading this setting value and haven't finished, there is no point in starting again.
  if (isLoading(settingID)) {
    return;
  } // Mark the fact that we are loading the field value


  setLoading(settingID, true);
  var optionsList = external_jQuery_default()(wrapper).find('.font-options__options-list'),
      inputs = optionsList.find('[data-value_entry]');
  inputs.each(function (key, input) {
    var $input = external_jQuery_default()(input);
    var valueEntry = $input.data('value_entry'); // In the case of select2, only the original selects have the data field, thus excluding select2 created select DOM elements

    if (typeof valueEntry === 'undefined' || valueEntry === '' || typeof value[valueEntry] === 'undefined') {
      return;
    } // We will do this only for numerical sub-fields.


    if (_.includes(['letter_spacing', 'line_height', 'font_size'], valueEntry)) {
      var subfieldValue = standardizeNumericalValue(value[valueEntry], input); // Make sure that the unit and value_unit attributes are in place.

      if (subfieldValue.unit !== '') {
        $input.data('value_unit', subfieldValue.unit);

        if (_.isEmpty($input.attr('unit'))) {
          $input.attr('unit', subfieldValue.unit);
        }
      } // If the field unit and value unit differ, we have some conversion to do.
      // We will convert the received value to the appropriate unit declared by the input.
      // We will use a guessed base size of 16px. Not an exact conversion, but it will have to do.


      var baseSize = 16;
      var subfieldUnit = $input.attr('unit').trim().toLowerCase();
      var subfieldValueUnit = $input.data('value_unit').trim().toLowerCase(); // The comparison is intentionally loose.

      if (subfieldUnit != subfieldValueUnit) {
        if (_.includes(['em', 'rem'], subfieldValueUnit) && 'px' === subfieldUnit) {
          // We will have to multiply the value.
          subfieldValue.value = round(subfieldValue.value * baseSize, customify.fonts.floatPrecision);
        } else if (_.includes(['em', 'rem'], subfieldUnit) && 'px' === subfieldValueUnit) {
          // We will have to divide the value.
          subfieldValue.value = round(subfieldValue.value / baseSize, customify.fonts.floatPrecision);
        }
      } // If this field has a min/max attribute we need to make sure that those attributes allow for the value we are trying to impose.


      if ($input.attr('min') && $input.attr('min') > subfieldValue.value) {
        $input.attr('min', subfieldValue.value);
      }

      if ($input.attr('max') && $input.attr('max') < subfieldValue.value) {
        $input.attr('max', subfieldValue.value);
      }

      $input.val(subfieldValue.value);
    } else {
      $input.val(value[valueEntry]);
    } // Mark this input as not touched by the user.


    $input.data('touched', false);
    $input.trigger('change', ['customify']);
  }); // Finished with the field value loading.

  setLoading(settingID, false);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/self-update-value.js


/**
 * Gather the value for our entire font field and save it in the setting.
 */

var selfUpdateValue = function selfUpdateValue(wrapper, settingID) {
  // If we are already self-updating this and we haven't finished, we need to stop here to prevent infinite loops
  // This call might have come from a subfield detecting the change thus triggering a further selfUpdateValue()
  // If we are loading this setting value and haven't finished,
  // there is no point in updating it as this would cause infinite loops.
  if (isUpdating(settingID) || isLoading(settingID)) {
    return;
  } // Mark the fact that we are self-updating the field value


  setUpdating(settingID, true);
  var optionsList = wrapper.find('.font-options__options-list');
  var inputs = optionsList.find('[data-value_entry]');
  var newFontData = {};
  wp.customize(settingID, function (setting) {
    newFontData = external_jQuery_default().extend(true, {}, setting());
    inputs.each(function (key, input) {
      var $input = external_jQuery_default()(input);
      var valueEntry = $input.data('value_entry');
      var value = $input.val(); // We only pick up subfields values that have been touched by the user, that are enabled (visible) or values that are missing in the oldValue.

      if (_.isUndefined(valueEntry) || $input.data('disabled') || !$input.data('touched') && !_.isUndefined(newFontData[valueEntry])) {
        return;
      }

      if ('font_family' === valueEntry) {
        // Get the src of the selected option.
        var src = external_jQuery_default()(input.options[input.selectedIndex]).data('src');

        if (src) {
          newFontData['src'] = src;
        } else {
          delete newFontData['src'];
        }
      }

      if (!_.isUndefined(value) && !_.isNull(value) && value !== '') {
        if (_.includes(['letter_spacing', 'line_height', 'font_size'], valueEntry)) {
          // Standardize the value.
          value = standardize_numerical_value_standardizeNumericalValue(value, input, false);
        }

        newFontData[valueEntry] = value;
      } else {
        delete newFontData[valueEntry];
      }
    }); // We don't need to store font variants or subsets list in the value
    // since we will get those from the global font details.

    delete newFontData['variants'];
    delete newFontData['subsets']; // We need to make sure that we don't "use" any variants not supported by the new font (values passed over from the old value).
    // Get the new font details

    var newFontDetails = getFontDetails(newFontData['font_family']); // Check the font variant

    if (typeof newFontData['font_variant'] !== 'undefined' && typeof newFontDetails.variants !== 'undefined' && Object.keys(newFontDetails.variants).length > 0) {
      // Make sure that the font_variant is a string, not a number.
      newFontData['font_variant'] = String(newFontData['font_variant']);

      if (!_.includes(newFontDetails.variants, newFontData['font_variant'])) {
        // The new font doesn't have this variant. Nor should the value.
        delete newFontData['font_variant'];
      }
    } else {
      // The new font has no variants. Nor should the value.
      delete newFontData['font_variant'];
    } // Update the Customizer setting value.


    setting.set(newFontData);
  }); // Finished with the field value self-updating.

  setUpdating(settingID, false);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/standardize-numerical-value.js

/**
 * Given a value we will standardize it to an array with 'value' and 'unit'.
 *
 * This is a mirror logic of the server-side one from Customify_Fonts_Global::standardizeNumericalValue()
 *
 * @param value
 * @param input Optional. The input this value was extracted from
 * @param valueFirst Optional. Whether to give higher priority to value related data, or to input related one.
 */

var standardize_numerical_value_standardizeNumericalValue = function standardizeNumericalValue(value) {
  var input = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  var valueFirst = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  var standardValue = {
    value: false,
    unit: false
  };

  if (_.includes(['', 'false', false], value)) {
    return standardValue;
  }

  if (!isNaN(value)) {
    standardValue.value = value;
  } else if (typeof value.value !== 'undefined') {
    standardValue.value = value.value;

    if (typeof value.unit !== 'undefined') {
      standardValue.unit = value.unit;
    }
  } else if (typeof value[0] !== 'undefined') {
    standardValue.value = value[0];

    if (typeof value[1] !== 'undefined') {
      standardValue.unit = value[1];
    }
  } else if (typeof value === 'string') {
    // We will get everything in front that is a valid part of a number (float including).
    var matches = value.match(/^([\d.\-+]+)(.+)/i);

    if (matches !== null && typeof matches[1] !== 'undefined') {
      standardValue.value = matches[1];

      if (!_.isEmpty(matches[2])) {
        standardValue.unit = matches[2];
      }
    } else {
      // If we could not extract anything useful we will trust the developer and leave it like that.
      standardValue.value = value;
    }
  }

  if (false !== input && (false === standardValue.unit || _.isEmpty(standardValue.unit))) {
    // If we are given an input, we will attempt to extract the unit from its attributes.
    var fallbackInputUnit = '';
    var $input = external_jQuery_default()(input);

    if (valueFirst) {
      if (!_.isEmpty($input.data('value_unit'))) {
        fallbackInputUnit = $input.data('value_unit');
      } else if (!_.isEmpty($input.attr('unit'))) {
        fallbackInputUnit = $input.attr('unit');
      }
    } else {
      if (!_.isEmpty($input.attr('unit'))) {
        fallbackInputUnit = $input.attr('unit');
      } else if (!_.isEmpty($input.data('value_unit'))) {
        fallbackInputUnit = $input.data('value_unit');
      }
    }

    standardValue.unit = fallbackInputUnit;
  } // Make sure that if we have a numerical value, it is a float.


  if (!isNaN(standardValue.value)) {
    standardValue.value = parseFloat(standardValue.value);
  }

  return standardValue;
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/update-font-head-title.js

/**
 * Update the title of the font field (the field head) with the new font family name.
 *
 * @param newFontDetails
 * @param wrapper
 */

var updateFontHeadTitle = function updateFontHeadTitle(newFontDetails, wrapper) {
  var fontTitleElement = wrapper.find('.font-options__head .font-options__font-title');
  var fontFamilyDisplay = newFontDetails.family;

  if (typeof newFontDetails.family_display === 'string' && newFontDetails.family_display.length) {
    fontFamilyDisplay = newFontDetails.family_display;
  }

  external_jQuery_default()(fontTitleElement).html(fontFamilyDisplay);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/update-variant-field.js

var fontVariantSelector = '.customify_font_weight';
/**
 * This function updates the data in font weight selector from the given <option> element
 *
 * @param newFontDetails
 * @param wrapper
 */

var updateVariantField = function updateVariantField(newFontDetails, wrapper) {
  var variants = typeof newFontDetails.variants !== 'undefined' ? newFontDetails.variants : [],
      fontVariantInput = wrapper.find(fontVariantSelector),
      selectedVariant = fontVariantInput.val() ? fontVariantInput.val() : '',
      newVariants = []; // We clear everything about this subfield.

  fontVariantInput.val(null).empty();

  if (fontVariantInput.hasClass("select2-hidden-accessible")) {
    fontVariantInput.select2('destroy');
  } // Mark this input as not touched by the user.


  fontVariantInput.data('touched', false);

  if (typeof variants === 'undefined' || Object.keys(variants).length < 2) {
    fontVariantInput.parent().hide();
    fontVariantInput.parent().prev('label').hide(); // Mark this input as disabled.

    fontVariantInput.data('disabled', true);
    return;
  }

  var variantAutoText = customify.l10n.fonts.variantAutoText; // Initialize the options with an empty one.

  newVariants.push({
    'id': '',
    'text': variantAutoText
  }); // we need to turn the data array into a specific form like [{id:"id", text:"Text"}]

  external_jQuery_default().each(variants, function (index, variant) {
    var newVariant = {
      'id': variant,
      // This is the option value.
      'text': variant
    }; // Leave the comparison loose.

    if (selectedVariant == variant) {
      newVariant.selected = true;
    }

    newVariants.push(newVariant);
  }); // Only reinitialize the select2.
  // No need to rebind on change or on input since those are still bound to the original HTML element.

  fontVariantInput.select2({
    data: newVariants
  });
  fontVariantInput.parent().show();
  fontVariantInput.parent().prev('label').show(); // Mark this input as enabled.

  fontVariantInput.data('disabled', false);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/fonts-service.js
var updating = {};
var loading = {};
var isLoading = function isLoading(settingID) {
  return !!updating[settingID];
};
var isUpdating = function isUpdating(settingID) {
  return !!loading[settingID];
};
var setLoading = function setLoading(settingID, value) {
  loading[settingID] = value;
};
var setUpdating = function setUpdating(settingID, value) {
  updating[settingID] = value;
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/utils/index.js













var wrapperSelector = '.font-options__wrapper';
var valueHolderSelector = '.customify_font_values';
var getSettingID = function getSettingID($element) {
  return getWrapper($element).find(valueHolderSelector).data('customize-setting-link');
};
var getWrapper = function getWrapper($element) {
  return $element.closest(wrapperSelector);
};
;// CONCATENATED MODULE: ./src/js/customizer/fonts/index.js





var fonts_wrapperSelector = '.font-options__wrapper';
var fonts_fontVariantSelector = '.customify_font_weight';
wp.customize.bind('ready', function () {
  initializeFonts();
  fonts_reloadConnectedFields();
});
var initializeFonts = function initializeFonts() {
  var $fontFields = external_jQuery_default()(fonts_wrapperSelector);
  $fontFields.each(function (i, obj) {
    var $fontField = external_jQuery_default()(obj);
    initializeFontFamilyField($fontField);
    initializeSubfields($fontField);
  });
  handleFontPopupToggle();
};

var initializeFontFamilyField = function initializeFontFamilyField($fontField) {
  var $fontFamilyField = $fontField.find('.customify_font_family');
  var familyPlaceholderText = customify.l10n.fonts.familyPlaceholderText; // Add the Google Fonts opts to each control

  addGoogleFontsToFontFamilyField($fontFamilyField); // Initialize the select2 field for the font family

  $fontFamilyField.select2({
    placeholder: familyPlaceholderText
  });
  $fontFamilyField.on('change', onFontFamilyChange);
  bindFontFamilySettingChange($fontFamilyField);
};

var initializeSubfields = function initializeSubfields($fontField) {
  var $variant = $fontField.find(fonts_fontVariantSelector);
  var $select = $fontField.find('select').not('select[class*=\' select2\'],select[class^=\'select2\']');
  var $range = $fontField.find('input[type="range"]'); // Initialize the select2 field for the font variant

  initSubfield($variant, true); // Initialize all the regular selects in the font subfields

  initSubfield($select, false); // Initialize the all the range fields in the font subfields

  initSubfield($range, false);
};

var addGoogleFontsToFontFamilyField = function addGoogleFontsToFontFamilyField($fontFamilyField) {
  var googleFontsOptions = wp.customize.settings['google_fonts_opts'];
  var $googleOptionsPlaceholder = $fontFamilyField.find('.google-fonts-opts-placeholder').first();

  if (typeof googleFontsOptions !== 'undefined' && $googleOptionsPlaceholder.length) {
    // Replace the placeholder with the HTML for the Google fonts select options.
    $googleOptionsPlaceholder.replaceWith(googleFontsOptions); // The active font family might be a Google font so we need to set the current value after we've added the options.

    var activeFontFamily = $fontFamilyField.data('active_font_family');

    if (typeof activeFontFamily !== 'undefined') {
      $fontFamilyField.val(activeFontFamily);
    }
  }
};

var onFontFamilyChange = function onFontFamilyChange(event) {
  var newFontFamily = event.target.value;
  var $target = external_jQuery_default()(event.target);
  var $wrapper = $target.closest(fonts_wrapperSelector); // Get the new font details

  var newFontDetails = getFontDetails(newFontFamily); // Update the font field head title (with the new font family name).

  updateFontHeadTitle(newFontDetails, $wrapper); // Update the variant subfield with the new options given by the selected font family.

  updateVariantField(newFontDetails, $wrapper);

  if (typeof who !== 'undefined' && who === 'customify') {// The change was triggered programmatically by Customify.
    // No need to self-update the value.
  } else {
    // Mark this input as touched by the user.
    external_jQuery_default()(event.target).data('touched', true); // Serialize subfield values and refresh the fonts in the preview window.

    selfUpdateValue($wrapper);
  }
};

var bindFontFamilySettingChange = function bindFontFamilySettingChange($fontFamilyField) {
  var $wrapper = $fontFamilyField.closest(fonts_wrapperSelector);
  var settingID = getSettingID($fontFamilyField);
  wp.customize(settingID, function (setting) {
    setting.bind(function (newValue, oldValue) {
      if (!isUpdating(settingID)) {
        loadFontValue($wrapper, newValue, settingID);
      }
    });
  });
};

var fonts_reloadConnectedFields = external_lodash_default().debounce(function () {
  var settingIDs = customify.fontPalettes.masterSettingIds;
  unbindConnectedFields(settingIDs);
  settingIDs.forEach(function (settingID) {
    wp.customize(settingID, function (parentSetting) {
      setCallback(settingID, function (newValue) {
        var settingConfig = getSetting(settingID);
        var connectedFields = settingConfig.connected_fields || {};
        Object.keys(connectedFields).forEach(function (key) {
          var connectedSettingID = connectedFields[key].setting_id;
          var connectedFieldData = customify.config.settings[connectedSettingID];
          var callbackFilter = getCallbackFilter(connectedFieldData);
          wp.customize(connectedSettingID, function (connectedSetting) {
            connectedSetting.set(callbackFilter(newValue));
          });
        });
      });
      parentSetting.bind(getCallback(settingID));
    });
  });
}, 30);
;// CONCATENATED MODULE: ./src/js/customizer/font-palettes/index.js

wp.customize.bind('ready', function () {
  initializeFontPalettes();
});

var initializeFontPalettes = function initializeFontPalettes() {
  // Handle the palette change logic.
  external_jQuery_default()('.js-font-palette input[name="sm_font_palette"]').on('change', onPaletteChange); // Handle the case where one clicks on the already selected palette - force a reset.

  external_jQuery_default()('.js-font-palette .customize-inside-control-row').on('click', function (event) {
    // Find the input
    var $target = external_jQuery_default()(event.target);
    var $input = $target.find('input[name="sm_font_palette"]');
    $input.trigger('change');
  }); // Handle the case when there is no selected font palette (like on a fresh installation without any demo data import).
  // In this case we want to hide the advanced tab.

  wp.customize('sm_font_palette', function (setting) {
    if (!setting()) {
      hideAdvancedFontPaletteControls();
    }
  });
};

var advancedTabSelector = '#sub-accordion-section-sm_font_palettes_section .sm-tabs__item[data-target="advanced"]';

var hideAdvancedFontPaletteControls = function hideAdvancedFontPaletteControls() {
  external_jQuery_default()(advancedTabSelector).css('visibility', 'hidden');
};

var showAdvancedFontPaletteControls = function showAdvancedFontPaletteControls() {
  external_jQuery_default()(advancedTabSelector).css('visibility', 'visible');
};

var onPaletteChange = function onPaletteChange() {
  // Make sure that the advanced tab is visible.
  showAdvancedFontPaletteControls(); // Take the fonts config for each setting and distribute it to each (master) setting.

  var fontsLogic = external_jQuery_default()(this).data('fonts_logic');
  external_jQuery_default().each(fontsLogic, function (settingID, config) {
    wp.customize(settingID, function (setting) {
      setting.set(config);
    });
  });
};
;// CONCATENATED MODULE: ./src/js/customizer/fields/color-select/index.js

var handleColorSelectFields = function handleColorSelectFields() {
  external_jQuery_default()('.js-color-select').each(function (i, obj) {
    convertToColorSelect(obj);
  });
};
var convertToColorSelect = function convertToColorSelect(element) {
  var $select = external_jQuery_default()(element);
  var $selectOptions = $select.find('option');
  var $colorSelect = external_jQuery_default()('<div class="customify-color-select">');
  var $optionsList = external_jQuery_default()('<div class="customify-color-select__option-list">');
  $selectOptions.each(function (i, option) {
    var $option = external_jQuery_default()(option);
    var label = $option.text();
    var value = $option.attr('value');
    var $colorSelectOptionLabel = external_jQuery_default()('<div class="customify-color-select__option-label">');
    var $colorSelectOption = external_jQuery_default()('<div class="customify-color-select__option">');
    $colorSelectOptionLabel.text(label).appendTo($colorSelectOption);
    $colorSelectOption.data('value', value).appendTo($optionsList);
    $colorSelectOption.addClass('customify-color-select__option--' + value);
  });
  $optionsList.appendTo($colorSelect);
  var $colorSelectOptions = $colorSelect.find('.customify-color-select__option');
  $colorSelectOptions.each(function (i, option) {
    var $colorSelectOption = external_jQuery_default()(option);
    var value = $colorSelectOption.data('value');
    $colorSelectOption.on('click', function () {
      $select.val(value).change();
    });
  });
  $colorSelect.insertBefore($select);
  $select.hide();

  function updateColorSelect() {
    var value = $select.val();
    var $colorSelectOption = $colorSelectOptions.filter(function (index, obj) {
      return external_jQuery_default()(obj).data('value') === value;
    });

    if ($colorSelectOption.length) {
      $colorSelectOptions.removeClass('customify-color-select__option--selected');
      $colorSelectOption.addClass('customify-color-select__option--selected');
    }
  }

  updateColorSelect();
  $select.on('change', updateColorSelect);
};
;// CONCATENATED MODULE: ./src/js/customizer/fields/range/index.js

var handleRangeFields = function handleRangeFields() {
  var rangeControlSelector = ".accordion-section-content[id*=\"".concat(customify.config.options_name, "\"], #sub-accordion-section-sm_color_palettes_section");
  external_jQuery_default()(rangeControlSelector).each(function (i, container) {
    var $rangeFields = external_jQuery_default()(container).find('input[type="range"]'); // For each range input add a number field (for preview mainly - but it can also be used for input)

    $rangeFields.each(function (i, obj) {
      var $range = external_jQuery_default()(obj);
      var $number = $range.siblings('.range-value');

      if (!$number.length) {
        $number = $range.clone();
        $number.attr('type', 'text').attr('class', 'range-value').removeAttr('data-value_entry');
        $number.data('source', $range);

        if ($range.first().attr('id')) {
          $number.attr('id', $range.first().attr('id') + '_number');
        }

        $number.insertAfter($range);
      } // Put the value into the number field.


      $range.on('input change', function (event) {
        if (event.target.value === $number.val()) {
          // Nothing to do if the values are identical.
          return;
        }

        $number.val(event.target.value);
      }); // When clicking outside the number field or on Enter.

      $number.on('blur keyup', onRangePreviewBlur);
    });
  });
};

function onRangePreviewBlur(event) {
  var $number = external_jQuery_default()(event.target);
  var $range = $number.data('source');

  if ('keyup' === event.type && event.keyCode !== 13) {
    return;
  }

  if (event.target.value === $range.val()) {
    // Nothing to do if the values are identical.
    return;
  }

  if (!hasValidValue($number)) {
    $number.val($range.val());
    shake($number);
  } else {
    // Do not mark this trigger as being programmatically triggered by Customify since it is a result of a user input.
    $range.val($number.val()).trigger('change');
  }
}

function hasValidValue($input) {
  var min = $input.attr('min');
  var max = $input.attr('max');
  var value = $input.val();

  if (typeof min !== 'undefined' && parseFloat(min) > parseFloat(value)) {
    return false;
  }

  if (typeof max !== 'undefined' && parseFloat(max) < parseFloat(value)) {
    return false;
  }

  return true;
}

function shake($field) {
  $field.addClass('input-shake input-error');
  $field.one('animationend', function () {
    $field.removeClass('input-shake input-error');
  });
}
;// CONCATENATED MODULE: ./src/js/customizer/fields/tabs/index.js

var handleTabs = function handleTabs() {
  external_jQuery_default()('.sm-tabs').each(function (i, obj) {
    var $wrapper = external_jQuery_default()(obj);
    var $section = $wrapper.closest('.control-section');
    var $tabs = $wrapper.children('.sm-tabs__item');
    var targets = $tabs.map(function (i, el) {
      var target = external_jQuery_default()(el).data('target');
      return "sm-view-".concat(target);
    });
    var targetClassnames = targets.toArray().join(" ");

    function setActiveTab($active) {
      var target = $active.data('target');
      $tabs.removeClass('sm-tabs__item--active');
      $active.addClass('sm-tabs__item--active');
      $section.removeClass(targetClassnames).addClass("sm-view-".concat(target));
    }

    $wrapper.on('click', '.sm-tabs__item', function (e) {
      e.preventDefault();
      setActiveTab(external_jQuery_default()(this));
    });
    setActiveTab($tabs.first());
  });
};
;// CONCATENATED MODULE: ./src/js/customizer/folding-fields.js

/**
 * This function will search for all the interdependend fields and make a bound between them.
 * So whenever a target is changed, it will take actions to the dependent fields.
 * @TODO  this is still written in a barbaric way, refactor when needed
 */

var handleFoldingFields = function handleFoldingFields() {
  if (_.isUndefined(customify.config) || _.isUndefined(customify.config.settings)) {
    return; // bail
  }

  (external_jQuery_default()).fn.reactor.defaults.compliant = function () {
    external_jQuery_default()(this).slideDown();
    external_jQuery_default()(this).find(':disabled').attr({
      disabled: false
    });
  };

  (external_jQuery_default()).fn.reactor.defaults.uncompliant = function () {
    external_jQuery_default()(this).slideUp();
    external_jQuery_default()(this).find(':enabled').attr({
      disabled: true
    });
  };

  var IS = external_jQuery_default().extend({}, (external_jQuery_default()).fn.reactor.helpers);

  var bindFoldingEvents = function bindFoldingEvents(parentID, field, relation) {
    var key = null;

    if (_.isString(field)) {
      key = field;
    } else if (!_.isUndefined(field.id)) {
      key = field.id;
    } else if (_.isString(field[0])) {
      key = field[0];
    } else {
      return; // no key, no fun
    }

    var value = 1,
        // by default we use 1 the most used value for checkboxes or inputs
    between = [0, 1]; // can only be `show` or `hide`

    var target_key = customify.config.options_name + '[' + key + ']';
    var target_type = customify.config.settings[target_key].type; // we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
    // but we also support a non-associative array like `array( $id, $value, $compare )`

    if (!_.isUndefined(field.value)) {
      value = field.value;
    } else if (!_.isUndefined(field[1]) && !_.isString(field[1])) {
      value = field[1];
    }

    if (!_.isUndefined(field.between)) {
      between = field.between;
    }
    /**
     * Now for each target we have, we will bind a change event to hide or show the dependent fields
     */


    var target_selector = '[data-customize-setting-link="' + customify.config.options_name + '[' + key + ']"]';

    switch (target_type) {
      case 'checkbox':
        external_jQuery_default()(parentID).reactIf(target_selector, function () {
          return external_jQuery_default()(this).is(':checked') == value;
        });
        break;

      case 'radio':
      case 'sm_radio':
      case 'sm_switch':
      case 'radio_image':
      case 'radio_html':
        // in case of an array of values we use the ( val in array) condition
        if (_.isObject(value)) {
          value = _.toArray(value);
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return value.indexOf(external_jQuery_default()(target_selector + ':checked').val()) !== -1;
          });
        } else {
          // in any other case we use a simple == comparison
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return external_jQuery_default()(target_selector + ':checked').val() == value;
          });
        }

        break;

      case 'range':
        var x = IS.Between(between[0], between[1]);
        external_jQuery_default()(parentID).reactIf(target_selector, x);
        break;

      default:
        // in case of an array of values we use the ( val in array) condition
        if (_.isObject(value)) {
          value = _.toArray(value);
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return value.indexOf(external_jQuery_default()(target_selector).val()) !== -1;
          });
        } else {
          // in any other case we use a simple == comparison
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return external_jQuery_default()(target_selector).val() == value;
          });
        }

        break;
    }

    external_jQuery_default()(target_selector).trigger('change', ['customify']);
    external_jQuery_default()('.reactor').trigger('change.reactor'); // triggers all events on load
  };

  external_jQuery_default().each(customify.config.settings, function (id, field) {
    /**
     * Here we have the id of the fields. but we know for sure that we just need his parent selector
     * So we just create it
     */
    var parentID = id.replace('[', '-');
    parentID = parentID.replace(']', '');
    parentID = '#customize-control-' + parentID + '_control'; // get only the fields that have a 'show_if' property

    if (field.hasOwnProperty('show_if')) {
      var relation = 'AND';

      if (!_.isUndefined(field.show_if.relation)) {
        relation = field.show_if.relation; // remove the relation property, we need the config to be array based only

        delete field.show_if.relation;
      }
      /**
       * The 'show_if' can be a simple array with one target like: [ id, value, comparison, action ]
       * Or it could be an array of multiple targets and we need to process both cases
       */


      if (!_.isUndefined(field.show_if.id)) {
        bindFoldingEvents(parentID, field.show_if, relation);
      } else if (_.isObject(field.show_if)) {
        external_jQuery_default().each(field.show_if, function (i, j) {
          bindFoldingEvents(parentID, j, relation);
        });
      }
    }
  });
};
;// CONCATENATED MODULE: ./src/js/customizer/scale-preview.js

var scalePreview = function scalePreview() {
  wp.customize.previewer.bind('synced', function () {
    scalePreviewIframe();
    wp.customize.previewedDevice.bind(scalePreviewIframe);
    external_jQuery_default()(window).on('resize', scalePreviewIframe);
  });
  external_jQuery_default()('.collapse-sidebar').on('click', function () {
    setTimeout(scalePreviewIframe, 300);
  });
};

var scalePreviewIframe = function scalePreviewIframe() {
  var $window = external_jQuery_default()(window);
  var $previewIframe = external_jQuery_default()('.wp-full-overlay'); // remove CSS properties that may have been previously added

  $previewIframe.find('iframe').css({
    width: '',
    height: '',
    transformOrigin: '',
    transform: ''
  }); // scaling of the site preview should be done only in desktop preview mode

  if (wp.customize.previewedDevice.get() !== 'desktop') {
    return;
  }

  var iframeWidth = $previewIframe.width();
  var windowWidth = $window.width();
  var windowHeight = $window.height(); // get the ratio between the site preview and actual browser width

  var scale = windowWidth / iframeWidth; // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the iframe and use CSS transforms to scale it back down

  if (iframeWidth > 720 && iframeWidth < 1100) {
    $previewIframe.find('iframe').css({
      width: iframeWidth * scale,
      height: windowHeight * scale,
      transformOrigin: 'left top',
      transform: 'scale(' + 1 / scale + ')'
    });
  }
};
;// CONCATENATED MODULE: ./src/js/customizer/utils/api-set-setting-value.js
/**
 * Set a setting value.
 *
 * Mostly used for resetting settings (via the reset buttons) but also for the preset (legacy) field.
 *
 * @param settingID
 * @param value
 */
var apiSetSettingValue = function apiSetSettingValue(settingID, value) {
  var setting = api(settingID),
      field = $('[data-customize-setting-link="' + settingID + '"]'),
      fieldClass = $(field).parent().attr('class');

  if (!_.isUndefined(fieldClass) && fieldClass === 'font-options__wrapper') {
    // if the value is a simple string it must be the font family
    if (_.isString(value)) {
      setting.set({
        'font_family': value
      });
    } else if (_.isObject(value)) {
      var standardValue = {}; // We will process each font property and update it

      _.each(value, function (val, key) {
        // We need to map the keys to the data attributes we are using - I know :(
        var mappedKey = key;

        switch (key) {
          case 'font-family':
            mappedKey = 'font_family';
            break;

          case 'font-size':
            mappedKey = 'font_size';
            break;

          case 'font-weight':
            mappedKey = 'font_variant';
            break;

          case 'letter-spacing':
            mappedKey = 'letter_spacing';
            break;

          case 'text-transform':
            mappedKey = 'text_transform';
            break;

          default:
            break;
        }

        standardValue[mappedKey] = val;
      });

      setting.set(standardValue);
    }
  } else {
    setting.set(value);
  }
};
;// CONCATENATED MODULE: ./src/js/customizer/create-reset-buttons.js


var createResetButtons = function createResetButtons() {
  var $document = external_jQuery_default()(document);
  var showResetButtons = external_jQuery_default()('button[data-action="reset_customify"]').length > 0;

  if (showResetButtons) {
    createResetPanelButtons();
    createResetSectionButtons();
    $document.on('click', '.js-reset-panel', onResetPanel);
    $document.on('click', '.js-reset-section', onResetSection);
    $document.on('click', '#customize-control-reset_customify button', onReset);
  }
};

function createResetPanelButtons() {
  external_jQuery_default()('.panel-meta').each(function (i, obj) {
    var $this = external_jQuery_default()(obj);
    var container = $this.parents('.control-panel');
    var id = container.attr('id');

    if (typeof id !== 'undefined') {
      id = id.replace('sub-accordion-panel-', '');
      id = id.replace('accordion-panel-', '');
      var $buttonWrapper = external_jQuery_default()('<li class="customize-control customize-control-reset"></li>');
      var $button = external_jQuery_default()('<button class="button js-reset-panel" data-panel="' + id + '"></button>');
      $button.text(customify.l10n.panelResetButton).appendTo($buttonWrapper);
      $this.parent().append($buttonWrapper);
    }
  });
}

function createResetSectionButtons() {
  external_jQuery_default()('.accordion-section-content').each(function (el, key) {
    var $this = external_jQuery_default()(this);
    var sectionID = $this.attr('id');

    if (_.isUndefined(sectionID) || sectionID.indexOf(customify.config.options_name) === -1) {
      return;
    }

    var id = sectionID.replace('sub-accordion-section-', '');
    var $button = external_jQuery_default()('<button class="button js-reset-section" data-section="' + id + '"></button>');
    var $buttonWrapper = external_jQuery_default()('<li class="customize-control customize-control-reset"></li>');
    $button.text(customify.l10n.sectionResetButton);
    $buttonWrapper.append($button);
    $this.append($buttonWrapper);
  });
}

function onReset(ev) {
  ev.preventDefault();
  var iAgree = confirm(customify.l10n.resetGlobalConfirmMessage);

  if (!iAgree) {
    return;
  }

  external_jQuery_default().each(api.settings.controls, function (key, ctrl) {
    var settingID = key.replace('_control', '');
    var setting = customify.config.settings[settingID];

    if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
      apiSetSettingValue(settingID, setting.default);
    }
  });
  api.previewer.save();
}

function onResetPanel(e) {
  e.preventDefault();
  var panelID = external_jQuery_default()(this).data('panel'),
      panel = api.panel(panelID),
      sections = panel.sections(),
      iAgree = confirm(customify.l10n.resetPanelConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (sections.length > 0) {
    external_jQuery_default().each(sections, function () {
      var controls = this.controls();

      if (controls.length > 0) {
        external_jQuery_default().each(controls, function (key, ctrl) {
          var settingID = ctrl.id.replace('_control', ''),
              setting = customify.config.settings[settingID];

          if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
            apiSetSettingValue(settingID, setting.default);
          }
        });
      }
    });
  }
}

function onResetSection(e) {
  e.preventDefault();
  var sectionID = external_jQuery_default()(this).data('section'),
      section = api.section(sectionID),
      controls = section.controls();
  var iAgree = confirm(customify.l10n.resetSectionConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (controls.length > 0) {
    external_jQuery_default().each(controls, function (key, ctrl) {
      var setting_id = ctrl.id.replace('_control', ''),
          setting = customify.config.settings[setting_id];

      if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
        apiSetSettingValue(setting_id, setting.default);
      }
    });
  }
}
;// CONCATENATED MODULE: ./src/js/customizer/index.js











wp.customize.bind('ready', function () {
  loadSettings();
  var settings = getSettings();
  var settingIDs = Object.keys(settings);
  bindConnectedFields(settingIDs);
  createResetButtons();
  handleRangeFields();
  handleColorSelectFields();
  handleTabs(); // @todo check reason for this timeout

  setTimeout(function () {
    handleFoldingFields();
  }, 1000); // Initialize simple select2 fields.

  external_jQuery_default()('.customify_select2').select2();
  scalePreview();
}); // expose API on sm.customizer global object



/***/ }),

/***/ 792:
/***/ (function(module) {

/**
 * chroma.js - JavaScript library for color conversions
 *
 * Copyright (c) 2011-2019, Gregor Aisch
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. The name Gregor Aisch may not be used to endorse or promote products
 * derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL GREGOR AISCH OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * -------------------------------------------------------
 *
 * chroma.js includes colors from colorbrewer2.org, which are released under
 * the following license:
 *
 * Copyright (c) 2002 Cynthia Brewer, Mark Harrower,
 * and The Pennsylvania State University.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * ------------------------------------------------------
 *
 * Named colors are taken from X11 Color Names.
 * http://www.w3.org/TR/css3-color/#svg-color
 *
 * @preserve
 */

(function (global, factory) {
     true ? module.exports = factory() :
    0;
}(this, (function () { 'use strict';

    var limit = function (x, min, max) {
        if ( min === void 0 ) min=0;
        if ( max === void 0 ) max=1;

        return x < min ? min : x > max ? max : x;
    };

    var clip_rgb = function (rgb) {
        rgb._clipped = false;
        rgb._unclipped = rgb.slice(0);
        for (var i=0; i<=3; i++) {
            if (i < 3) {
                if (rgb[i] < 0 || rgb[i] > 255) { rgb._clipped = true; }
                rgb[i] = limit(rgb[i], 0, 255);
            } else if (i === 3) {
                rgb[i] = limit(rgb[i], 0, 1);
            }
        }
        return rgb;
    };

    // ported from jQuery's $.type
    var classToType = {};
    for (var i = 0, list = ['Boolean', 'Number', 'String', 'Function', 'Array', 'Date', 'RegExp', 'Undefined', 'Null']; i < list.length; i += 1) {
        var name = list[i];

        classToType[("[object " + name + "]")] = name.toLowerCase();
    }
    var type = function(obj) {
        return classToType[Object.prototype.toString.call(obj)] || "object";
    };

    var unpack = function (args, keyOrder) {
        if ( keyOrder === void 0 ) keyOrder=null;

    	// if called with more than 3 arguments, we return the arguments
        if (args.length >= 3) { return Array.prototype.slice.call(args); }
        // with less than 3 args we check if first arg is object
        // and use the keyOrder string to extract and sort properties
    	if (type(args[0]) == 'object' && keyOrder) {
    		return keyOrder.split('')
    			.filter(function (k) { return args[0][k] !== undefined; })
    			.map(function (k) { return args[0][k]; });
    	}
    	// otherwise we just return the first argument
    	// (which we suppose is an array of args)
        return args[0];
    };

    var last = function (args) {
        if (args.length < 2) { return null; }
        var l = args.length-1;
        if (type(args[l]) == 'string') { return args[l].toLowerCase(); }
        return null;
    };

    var PI = Math.PI;

    var utils = {
    	clip_rgb: clip_rgb,
    	limit: limit,
    	type: type,
    	unpack: unpack,
    	last: last,
    	PI: PI,
    	TWOPI: PI*2,
    	PITHIRD: PI/3,
    	DEG2RAD: PI / 180,
    	RAD2DEG: 180 / PI
    };

    var input = {
    	format: {},
    	autodetect: []
    };

    var last$1 = utils.last;
    var clip_rgb$1 = utils.clip_rgb;
    var type$1 = utils.type;


    var Color = function Color() {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var me = this;
        if (type$1(args[0]) === 'object' &&
            args[0].constructor &&
            args[0].constructor === this.constructor) {
            // the argument is already a Color instance
            return args[0];
        }

        // last argument could be the mode
        var mode = last$1(args);
        var autodetect = false;

        if (!mode) {
            autodetect = true;
            if (!input.sorted) {
                input.autodetect = input.autodetect.sort(function (a,b) { return b.p - a.p; });
                input.sorted = true;
            }
            // auto-detect format
            for (var i = 0, list = input.autodetect; i < list.length; i += 1) {
                var chk = list[i];

                mode = chk.test.apply(chk, args);
                if (mode) { break; }
            }
        }

        if (input.format[mode]) {
            var rgb = input.format[mode].apply(null, autodetect ? args : args.slice(0,-1));
            me._rgb = clip_rgb$1(rgb);
        } else {
            throw new Error('unknown format: '+args);
        }

        // add alpha channel
        if (me._rgb.length === 3) { me._rgb.push(1); }
    };

    Color.prototype.toString = function toString () {
        if (type$1(this.hex) == 'function') { return this.hex(); }
        return ("[" + (this._rgb.join(',')) + "]");
    };

    var Color_1 = Color;

    var chroma = function () {
    	var args = [], len = arguments.length;
    	while ( len-- ) args[ len ] = arguments[ len ];

    	return new (Function.prototype.bind.apply( chroma.Color, [ null ].concat( args) ));
    };

    chroma.Color = Color_1;
    chroma.version = '2.1.0';

    var chroma_1 = chroma;

    var unpack$1 = utils.unpack;
    var max = Math.max;

    var rgb2cmyk = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$1(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        r = r / 255;
        g = g / 255;
        b = b / 255;
        var k = 1 - max(r,max(g,b));
        var f = k < 1 ? 1 / (1-k) : 0;
        var c = (1-r-k) * f;
        var m = (1-g-k) * f;
        var y = (1-b-k) * f;
        return [c,m,y,k];
    };

    var rgb2cmyk_1 = rgb2cmyk;

    var unpack$2 = utils.unpack;

    var cmyk2rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        args = unpack$2(args, 'cmyk');
        var c = args[0];
        var m = args[1];
        var y = args[2];
        var k = args[3];
        var alpha = args.length > 4 ? args[4] : 1;
        if (k === 1) { return [0,0,0,alpha]; }
        return [
            c >= 1 ? 0 : 255 * (1-c) * (1-k), // r
            m >= 1 ? 0 : 255 * (1-m) * (1-k), // g
            y >= 1 ? 0 : 255 * (1-y) * (1-k), // b
            alpha
        ];
    };

    var cmyk2rgb_1 = cmyk2rgb;

    var unpack$3 = utils.unpack;
    var type$2 = utils.type;



    Color_1.prototype.cmyk = function() {
        return rgb2cmyk_1(this._rgb);
    };

    chroma_1.cmyk = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['cmyk']) ));
    };

    input.format.cmyk = cmyk2rgb_1;

    input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$3(args, 'cmyk');
            if (type$2(args) === 'array' && args.length === 4) {
                return 'cmyk';
            }
        }
    });

    var unpack$4 = utils.unpack;
    var last$2 = utils.last;
    var rnd = function (a) { return Math.round(a*100)/100; };

    /*
     * supported arguments:
     * - hsl2css(h,s,l)
     * - hsl2css(h,s,l,a)
     * - hsl2css([h,s,l], mode)
     * - hsl2css([h,s,l,a], mode)
     * - hsl2css({h,s,l,a}, mode)
     */
    var hsl2css = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var hsla = unpack$4(args, 'hsla');
        var mode = last$2(args) || 'lsa';
        hsla[0] = rnd(hsla[0] || 0);
        hsla[1] = rnd(hsla[1]*100) + '%';
        hsla[2] = rnd(hsla[2]*100) + '%';
        if (mode === 'hsla' || (hsla.length > 3 && hsla[3]<1)) {
            hsla[3] = hsla.length > 3 ? hsla[3] : 1;
            mode = 'hsla';
        } else {
            hsla.length = 3;
        }
        return (mode + "(" + (hsla.join(',')) + ")");
    };

    var hsl2css_1 = hsl2css;

    var unpack$5 = utils.unpack;

    /*
     * supported arguments:
     * - rgb2hsl(r,g,b)
     * - rgb2hsl(r,g,b,a)
     * - rgb2hsl([r,g,b])
     * - rgb2hsl([r,g,b,a])
     * - rgb2hsl({r,g,b,a})
     */
    var rgb2hsl = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        args = unpack$5(args, 'rgba');
        var r = args[0];
        var g = args[1];
        var b = args[2];

        r /= 255;
        g /= 255;
        b /= 255;

        var min = Math.min(r, g, b);
        var max = Math.max(r, g, b);

        var l = (max + min) / 2;
        var s, h;

        if (max === min){
            s = 0;
            h = Number.NaN;
        } else {
            s = l < 0.5 ? (max - min) / (max + min) : (max - min) / (2 - max - min);
        }

        if (r == max) { h = (g - b) / (max - min); }
        else if (g == max) { h = 2 + (b - r) / (max - min); }
        else if (b == max) { h = 4 + (r - g) / (max - min); }

        h *= 60;
        if (h < 0) { h += 360; }
        if (args.length>3 && args[3]!==undefined) { return [h,s,l,args[3]]; }
        return [h,s,l];
    };

    var rgb2hsl_1 = rgb2hsl;

    var unpack$6 = utils.unpack;
    var last$3 = utils.last;


    var round = Math.round;

    /*
     * supported arguments:
     * - rgb2css(r,g,b)
     * - rgb2css(r,g,b,a)
     * - rgb2css([r,g,b], mode)
     * - rgb2css([r,g,b,a], mode)
     * - rgb2css({r,g,b,a}, mode)
     */
    var rgb2css = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var rgba = unpack$6(args, 'rgba');
        var mode = last$3(args) || 'rgb';
        if (mode.substr(0,3) == 'hsl') {
            return hsl2css_1(rgb2hsl_1(rgba), mode);
        }
        rgba[0] = round(rgba[0]);
        rgba[1] = round(rgba[1]);
        rgba[2] = round(rgba[2]);
        if (mode === 'rgba' || (rgba.length > 3 && rgba[3]<1)) {
            rgba[3] = rgba.length > 3 ? rgba[3] : 1;
            mode = 'rgba';
        }
        return (mode + "(" + (rgba.slice(0,mode==='rgb'?3:4).join(',')) + ")");
    };

    var rgb2css_1 = rgb2css;

    var unpack$7 = utils.unpack;
    var round$1 = Math.round;

    var hsl2rgb = function () {
        var assign;

        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];
        args = unpack$7(args, 'hsl');
        var h = args[0];
        var s = args[1];
        var l = args[2];
        var r,g,b;
        if (s === 0) {
            r = g = b = l*255;
        } else {
            var t3 = [0,0,0];
            var c = [0,0,0];
            var t2 = l < 0.5 ? l * (1+s) : l+s-l*s;
            var t1 = 2 * l - t2;
            var h_ = h / 360;
            t3[0] = h_ + 1/3;
            t3[1] = h_;
            t3[2] = h_ - 1/3;
            for (var i=0; i<3; i++) {
                if (t3[i] < 0) { t3[i] += 1; }
                if (t3[i] > 1) { t3[i] -= 1; }
                if (6 * t3[i] < 1)
                    { c[i] = t1 + (t2 - t1) * 6 * t3[i]; }
                else if (2 * t3[i] < 1)
                    { c[i] = t2; }
                else if (3 * t3[i] < 2)
                    { c[i] = t1 + (t2 - t1) * ((2 / 3) - t3[i]) * 6; }
                else
                    { c[i] = t1; }
            }
            (assign = [round$1(c[0]*255),round$1(c[1]*255),round$1(c[2]*255)], r = assign[0], g = assign[1], b = assign[2]);
        }
        if (args.length > 3) {
            // keep alpha channel
            return [r,g,b,args[3]];
        }
        return [r,g,b,1];
    };

    var hsl2rgb_1 = hsl2rgb;

    var RE_RGB = /^rgb\(\s*(-?\d+),\s*(-?\d+)\s*,\s*(-?\d+)\s*\)$/;
    var RE_RGBA = /^rgba\(\s*(-?\d+),\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*([01]|[01]?\.\d+)\)$/;
    var RE_RGB_PCT = /^rgb\(\s*(-?\d+(?:\.\d+)?)%,\s*(-?\d+(?:\.\d+)?)%\s*,\s*(-?\d+(?:\.\d+)?)%\s*\)$/;
    var RE_RGBA_PCT = /^rgba\(\s*(-?\d+(?:\.\d+)?)%,\s*(-?\d+(?:\.\d+)?)%\s*,\s*(-?\d+(?:\.\d+)?)%\s*,\s*([01]|[01]?\.\d+)\)$/;
    var RE_HSL = /^hsl\(\s*(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)%\s*,\s*(-?\d+(?:\.\d+)?)%\s*\)$/;
    var RE_HSLA = /^hsla\(\s*(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)%\s*,\s*(-?\d+(?:\.\d+)?)%\s*,\s*([01]|[01]?\.\d+)\)$/;

    var round$2 = Math.round;

    var css2rgb = function (css) {
        css = css.toLowerCase().trim();
        var m;

        if (input.format.named) {
            try {
                return input.format.named(css);
            } catch (e) {
                // eslint-disable-next-line
            }
        }

        // rgb(250,20,0)
        if ((m = css.match(RE_RGB))) {
            var rgb = m.slice(1,4);
            for (var i=0; i<3; i++) {
                rgb[i] = +rgb[i];
            }
            rgb[3] = 1;  // default alpha
            return rgb;
        }

        // rgba(250,20,0,0.4)
        if ((m = css.match(RE_RGBA))) {
            var rgb$1 = m.slice(1,5);
            for (var i$1=0; i$1<4; i$1++) {
                rgb$1[i$1] = +rgb$1[i$1];
            }
            return rgb$1;
        }

        // rgb(100%,0%,0%)
        if ((m = css.match(RE_RGB_PCT))) {
            var rgb$2 = m.slice(1,4);
            for (var i$2=0; i$2<3; i$2++) {
                rgb$2[i$2] = round$2(rgb$2[i$2] * 2.55);
            }
            rgb$2[3] = 1;  // default alpha
            return rgb$2;
        }

        // rgba(100%,0%,0%,0.4)
        if ((m = css.match(RE_RGBA_PCT))) {
            var rgb$3 = m.slice(1,5);
            for (var i$3=0; i$3<3; i$3++) {
                rgb$3[i$3] = round$2(rgb$3[i$3] * 2.55);
            }
            rgb$3[3] = +rgb$3[3];
            return rgb$3;
        }

        // hsl(0,100%,50%)
        if ((m = css.match(RE_HSL))) {
            var hsl = m.slice(1,4);
            hsl[1] *= 0.01;
            hsl[2] *= 0.01;
            var rgb$4 = hsl2rgb_1(hsl);
            rgb$4[3] = 1;
            return rgb$4;
        }

        // hsla(0,100%,50%,0.5)
        if ((m = css.match(RE_HSLA))) {
            var hsl$1 = m.slice(1,4);
            hsl$1[1] *= 0.01;
            hsl$1[2] *= 0.01;
            var rgb$5 = hsl2rgb_1(hsl$1);
            rgb$5[3] = +m[4];  // default alpha = 1
            return rgb$5;
        }
    };

    css2rgb.test = function (s) {
        return RE_RGB.test(s) ||
            RE_RGBA.test(s) ||
            RE_RGB_PCT.test(s) ||
            RE_RGBA_PCT.test(s) ||
            RE_HSL.test(s) ||
            RE_HSLA.test(s);
    };

    var css2rgb_1 = css2rgb;

    var type$3 = utils.type;




    Color_1.prototype.css = function(mode) {
        return rgb2css_1(this._rgb, mode);
    };

    chroma_1.css = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['css']) ));
    };

    input.format.css = css2rgb_1;

    input.autodetect.push({
        p: 5,
        test: function (h) {
            var rest = [], len = arguments.length - 1;
            while ( len-- > 0 ) rest[ len ] = arguments[ len + 1 ];

            if (!rest.length && type$3(h) === 'string' && css2rgb_1.test(h)) {
                return 'css';
            }
        }
    });

    var unpack$8 = utils.unpack;

    input.format.gl = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var rgb = unpack$8(args, 'rgba');
        rgb[0] *= 255;
        rgb[1] *= 255;
        rgb[2] *= 255;
        return rgb;
    };

    chroma_1.gl = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['gl']) ));
    };

    Color_1.prototype.gl = function() {
        var rgb = this._rgb;
        return [rgb[0]/255, rgb[1]/255, rgb[2]/255, rgb[3]];
    };

    var unpack$9 = utils.unpack;

    var rgb2hcg = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$9(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        var min = Math.min(r, g, b);
        var max = Math.max(r, g, b);
        var delta = max - min;
        var c = delta * 100 / 255;
        var _g = min / (255 - delta) * 100;
        var h;
        if (delta === 0) {
            h = Number.NaN;
        } else {
            if (r === max) { h = (g - b) / delta; }
            if (g === max) { h = 2+(b - r) / delta; }
            if (b === max) { h = 4+(r - g) / delta; }
            h *= 60;
            if (h < 0) { h += 360; }
        }
        return [h, c, _g];
    };

    var rgb2hcg_1 = rgb2hcg;

    var unpack$a = utils.unpack;
    var floor = Math.floor;

    /*
     * this is basically just HSV with some minor tweaks
     *
     * hue.. [0..360]
     * chroma .. [0..1]
     * grayness .. [0..1]
     */

    var hcg2rgb = function () {
        var assign, assign$1, assign$2, assign$3, assign$4, assign$5;

        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];
        args = unpack$a(args, 'hcg');
        var h = args[0];
        var c = args[1];
        var _g = args[2];
        var r,g,b;
        _g = _g * 255;
        var _c = c * 255;
        if (c === 0) {
            r = g = b = _g;
        } else {
            if (h === 360) { h = 0; }
            if (h > 360) { h -= 360; }
            if (h < 0) { h += 360; }
            h /= 60;
            var i = floor(h);
            var f = h - i;
            var p = _g * (1 - c);
            var q = p + _c * (1 - f);
            var t = p + _c * f;
            var v = p + _c;
            switch (i) {
                case 0: (assign = [v, t, p], r = assign[0], g = assign[1], b = assign[2]); break
                case 1: (assign$1 = [q, v, p], r = assign$1[0], g = assign$1[1], b = assign$1[2]); break
                case 2: (assign$2 = [p, v, t], r = assign$2[0], g = assign$2[1], b = assign$2[2]); break
                case 3: (assign$3 = [p, q, v], r = assign$3[0], g = assign$3[1], b = assign$3[2]); break
                case 4: (assign$4 = [t, p, v], r = assign$4[0], g = assign$4[1], b = assign$4[2]); break
                case 5: (assign$5 = [v, p, q], r = assign$5[0], g = assign$5[1], b = assign$5[2]); break
            }
        }
        return [r, g, b, args.length > 3 ? args[3] : 1];
    };

    var hcg2rgb_1 = hcg2rgb;

    var unpack$b = utils.unpack;
    var type$4 = utils.type;






    Color_1.prototype.hcg = function() {
        return rgb2hcg_1(this._rgb);
    };

    chroma_1.hcg = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hcg']) ));
    };

    input.format.hcg = hcg2rgb_1;

    input.autodetect.push({
        p: 1,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$b(args, 'hcg');
            if (type$4(args) === 'array' && args.length === 3) {
                return 'hcg';
            }
        }
    });

    var unpack$c = utils.unpack;
    var last$4 = utils.last;
    var round$3 = Math.round;

    var rgb2hex = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$c(args, 'rgba');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        var a = ref[3];
        var mode = last$4(args) || 'auto';
        if (a === undefined) { a = 1; }
        if (mode === 'auto') {
            mode = a < 1 ? 'rgba' : 'rgb';
        }
        r = round$3(r);
        g = round$3(g);
        b = round$3(b);
        var u = r << 16 | g << 8 | b;
        var str = "000000" + u.toString(16); //#.toUpperCase();
        str = str.substr(str.length - 6);
        var hxa = '0' + round$3(a * 255).toString(16);
        hxa = hxa.substr(hxa.length - 2);
        switch (mode.toLowerCase()) {
            case 'rgba': return ("#" + str + hxa);
            case 'argb': return ("#" + hxa + str);
            default: return ("#" + str);
        }
    };

    var rgb2hex_1 = rgb2hex;

    var RE_HEX = /^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
    var RE_HEXA = /^#?([A-Fa-f0-9]{8}|[A-Fa-f0-9]{4})$/;

    var hex2rgb = function (hex) {
        if (hex.match(RE_HEX)) {
            // remove optional leading #
            if (hex.length === 4 || hex.length === 7) {
                hex = hex.substr(1);
            }
            // expand short-notation to full six-digit
            if (hex.length === 3) {
                hex = hex.split('');
                hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
            }
            var u = parseInt(hex, 16);
            var r = u >> 16;
            var g = u >> 8 & 0xFF;
            var b = u & 0xFF;
            return [r,g,b,1];
        }

        // match rgba hex format, eg #FF000077
        if (hex.match(RE_HEXA)) {
            if (hex.length === 5 || hex.length === 9) {
                // remove optional leading #
                hex = hex.substr(1);
            }
            // expand short-notation to full eight-digit
            if (hex.length === 4) {
                hex = hex.split('');
                hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2]+hex[3]+hex[3];
            }
            var u$1 = parseInt(hex, 16);
            var r$1 = u$1 >> 24 & 0xFF;
            var g$1 = u$1 >> 16 & 0xFF;
            var b$1 = u$1 >> 8 & 0xFF;
            var a = Math.round((u$1 & 0xFF) / 0xFF * 100) / 100;
            return [r$1,g$1,b$1,a];
        }

        // we used to check for css colors here
        // if _input.css? and rgb = _input.css hex
        //     return rgb

        throw new Error(("unknown hex color: " + hex));
    };

    var hex2rgb_1 = hex2rgb;

    var type$5 = utils.type;




    Color_1.prototype.hex = function(mode) {
        return rgb2hex_1(this._rgb, mode);
    };

    chroma_1.hex = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hex']) ));
    };

    input.format.hex = hex2rgb_1;
    input.autodetect.push({
        p: 4,
        test: function (h) {
            var rest = [], len = arguments.length - 1;
            while ( len-- > 0 ) rest[ len ] = arguments[ len + 1 ];

            if (!rest.length && type$5(h) === 'string' && [3,4,5,6,7,8,9].indexOf(h.length) >= 0) {
                return 'hex';
            }
        }
    });

    var unpack$d = utils.unpack;
    var TWOPI = utils.TWOPI;
    var min = Math.min;
    var sqrt = Math.sqrt;
    var acos = Math.acos;

    var rgb2hsi = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        /*
        borrowed from here:
        http://hummer.stanford.edu/museinfo/doc/examples/humdrum/keyscape2/rgb2hsi.cpp
        */
        var ref = unpack$d(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        r /= 255;
        g /= 255;
        b /= 255;
        var h;
        var min_ = min(r,g,b);
        var i = (r+g+b) / 3;
        var s = i > 0 ? 1 - min_/i : 0;
        if (s === 0) {
            h = NaN;
        } else {
            h = ((r-g)+(r-b)) / 2;
            h /= sqrt((r-g)*(r-g) + (r-b)*(g-b));
            h = acos(h);
            if (b > g) {
                h = TWOPI - h;
            }
            h /= TWOPI;
        }
        return [h*360,s,i];
    };

    var rgb2hsi_1 = rgb2hsi;

    var unpack$e = utils.unpack;
    var limit$1 = utils.limit;
    var TWOPI$1 = utils.TWOPI;
    var PITHIRD = utils.PITHIRD;
    var cos = Math.cos;

    /*
     * hue [0..360]
     * saturation [0..1]
     * intensity [0..1]
     */
    var hsi2rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        /*
        borrowed from here:
        http://hummer.stanford.edu/museinfo/doc/examples/humdrum/keyscape2/hsi2rgb.cpp
        */
        args = unpack$e(args, 'hsi');
        var h = args[0];
        var s = args[1];
        var i = args[2];
        var r,g,b;

        if (isNaN(h)) { h = 0; }
        if (isNaN(s)) { s = 0; }
        // normalize hue
        if (h > 360) { h -= 360; }
        if (h < 0) { h += 360; }
        h /= 360;
        if (h < 1/3) {
            b = (1-s)/3;
            r = (1+s*cos(TWOPI$1*h)/cos(PITHIRD-TWOPI$1*h))/3;
            g = 1 - (b+r);
        } else if (h < 2/3) {
            h -= 1/3;
            r = (1-s)/3;
            g = (1+s*cos(TWOPI$1*h)/cos(PITHIRD-TWOPI$1*h))/3;
            b = 1 - (r+g);
        } else {
            h -= 2/3;
            g = (1-s)/3;
            b = (1+s*cos(TWOPI$1*h)/cos(PITHIRD-TWOPI$1*h))/3;
            r = 1 - (g+b);
        }
        r = limit$1(i*r*3);
        g = limit$1(i*g*3);
        b = limit$1(i*b*3);
        return [r*255, g*255, b*255, args.length > 3 ? args[3] : 1];
    };

    var hsi2rgb_1 = hsi2rgb;

    var unpack$f = utils.unpack;
    var type$6 = utils.type;






    Color_1.prototype.hsi = function() {
        return rgb2hsi_1(this._rgb);
    };

    chroma_1.hsi = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hsi']) ));
    };

    input.format.hsi = hsi2rgb_1;

    input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$f(args, 'hsi');
            if (type$6(args) === 'array' && args.length === 3) {
                return 'hsi';
            }
        }
    });

    var unpack$g = utils.unpack;
    var type$7 = utils.type;






    Color_1.prototype.hsl = function() {
        return rgb2hsl_1(this._rgb);
    };

    chroma_1.hsl = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hsl']) ));
    };

    input.format.hsl = hsl2rgb_1;

    input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$g(args, 'hsl');
            if (type$7(args) === 'array' && args.length === 3) {
                return 'hsl';
            }
        }
    });

    var unpack$h = utils.unpack;
    var min$1 = Math.min;
    var max$1 = Math.max;

    /*
     * supported arguments:
     * - rgb2hsv(r,g,b)
     * - rgb2hsv([r,g,b])
     * - rgb2hsv({r,g,b})
     */
    var rgb2hsl$1 = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        args = unpack$h(args, 'rgb');
        var r = args[0];
        var g = args[1];
        var b = args[2];
        var min_ = min$1(r, g, b);
        var max_ = max$1(r, g, b);
        var delta = max_ - min_;
        var h,s,v;
        v = max_ / 255.0;
        if (max_ === 0) {
            h = Number.NaN;
            s = 0;
        } else {
            s = delta / max_;
            if (r === max_) { h = (g - b) / delta; }
            if (g === max_) { h = 2+(b - r) / delta; }
            if (b === max_) { h = 4+(r - g) / delta; }
            h *= 60;
            if (h < 0) { h += 360; }
        }
        return [h, s, v]
    };

    var rgb2hsv = rgb2hsl$1;

    var unpack$i = utils.unpack;
    var floor$1 = Math.floor;

    var hsv2rgb = function () {
        var assign, assign$1, assign$2, assign$3, assign$4, assign$5;

        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];
        args = unpack$i(args, 'hsv');
        var h = args[0];
        var s = args[1];
        var v = args[2];
        var r,g,b;
        v *= 255;
        if (s === 0) {
            r = g = b = v;
        } else {
            if (h === 360) { h = 0; }
            if (h > 360) { h -= 360; }
            if (h < 0) { h += 360; }
            h /= 60;

            var i = floor$1(h);
            var f = h - i;
            var p = v * (1 - s);
            var q = v * (1 - s * f);
            var t = v * (1 - s * (1 - f));

            switch (i) {
                case 0: (assign = [v, t, p], r = assign[0], g = assign[1], b = assign[2]); break
                case 1: (assign$1 = [q, v, p], r = assign$1[0], g = assign$1[1], b = assign$1[2]); break
                case 2: (assign$2 = [p, v, t], r = assign$2[0], g = assign$2[1], b = assign$2[2]); break
                case 3: (assign$3 = [p, q, v], r = assign$3[0], g = assign$3[1], b = assign$3[2]); break
                case 4: (assign$4 = [t, p, v], r = assign$4[0], g = assign$4[1], b = assign$4[2]); break
                case 5: (assign$5 = [v, p, q], r = assign$5[0], g = assign$5[1], b = assign$5[2]); break
            }
        }
        return [r,g,b,args.length > 3?args[3]:1];
    };

    var hsv2rgb_1 = hsv2rgb;

    var unpack$j = utils.unpack;
    var type$8 = utils.type;






    Color_1.prototype.hsv = function() {
        return rgb2hsv(this._rgb);
    };

    chroma_1.hsv = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hsv']) ));
    };

    input.format.hsv = hsv2rgb_1;

    input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$j(args, 'hsv');
            if (type$8(args) === 'array' && args.length === 3) {
                return 'hsv';
            }
        }
    });

    var labConstants = {
        // Corresponds roughly to RGB brighter/darker
        Kn: 18,

        // D65 standard referent
        Xn: 0.950470,
        Yn: 1,
        Zn: 1.088830,

        t0: 0.137931034,  // 4 / 29
        t1: 0.206896552,  // 6 / 29
        t2: 0.12841855,   // 3 * t1 * t1
        t3: 0.008856452,  // t1 * t1 * t1
    };

    var unpack$k = utils.unpack;
    var pow = Math.pow;

    var rgb2lab = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$k(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        var ref$1 = rgb2xyz(r,g,b);
        var x = ref$1[0];
        var y = ref$1[1];
        var z = ref$1[2];
        var l = 116 * y - 16;
        return [l < 0 ? 0 : l, 500 * (x - y), 200 * (y - z)];
    };

    var rgb_xyz = function (r) {
        if ((r /= 255) <= 0.04045) { return r / 12.92; }
        return pow((r + 0.055) / 1.055, 2.4);
    };

    var xyz_lab = function (t) {
        if (t > labConstants.t3) { return pow(t, 1 / 3); }
        return t / labConstants.t2 + labConstants.t0;
    };

    var rgb2xyz = function (r,g,b) {
        r = rgb_xyz(r);
        g = rgb_xyz(g);
        b = rgb_xyz(b);
        var x = xyz_lab((0.4124564 * r + 0.3575761 * g + 0.1804375 * b) / labConstants.Xn);
        var y = xyz_lab((0.2126729 * r + 0.7151522 * g + 0.0721750 * b) / labConstants.Yn);
        var z = xyz_lab((0.0193339 * r + 0.1191920 * g + 0.9503041 * b) / labConstants.Zn);
        return [x,y,z];
    };

    var rgb2lab_1 = rgb2lab;

    var unpack$l = utils.unpack;
    var pow$1 = Math.pow;

    /*
     * L* [0..100]
     * a [-100..100]
     * b [-100..100]
     */
    var lab2rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        args = unpack$l(args, 'lab');
        var l = args[0];
        var a = args[1];
        var b = args[2];
        var x,y,z, r,g,b_;

        y = (l + 16) / 116;
        x = isNaN(a) ? y : y + a / 500;
        z = isNaN(b) ? y : y - b / 200;

        y = labConstants.Yn * lab_xyz(y);
        x = labConstants.Xn * lab_xyz(x);
        z = labConstants.Zn * lab_xyz(z);

        r = xyz_rgb(3.2404542 * x - 1.5371385 * y - 0.4985314 * z);  // D65 -> sRGB
        g = xyz_rgb(-0.9692660 * x + 1.8760108 * y + 0.0415560 * z);
        b_ = xyz_rgb(0.0556434 * x - 0.2040259 * y + 1.0572252 * z);

        return [r,g,b_,args.length > 3 ? args[3] : 1];
    };

    var xyz_rgb = function (r) {
        return 255 * (r <= 0.00304 ? 12.92 * r : 1.055 * pow$1(r, 1 / 2.4) - 0.055)
    };

    var lab_xyz = function (t) {
        return t > labConstants.t1 ? t * t * t : labConstants.t2 * (t - labConstants.t0)
    };

    var lab2rgb_1 = lab2rgb;

    var unpack$m = utils.unpack;
    var type$9 = utils.type;






    Color_1.prototype.lab = function() {
        return rgb2lab_1(this._rgb);
    };

    chroma_1.lab = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['lab']) ));
    };

    input.format.lab = lab2rgb_1;

    input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$m(args, 'lab');
            if (type$9(args) === 'array' && args.length === 3) {
                return 'lab';
            }
        }
    });

    var unpack$n = utils.unpack;
    var RAD2DEG = utils.RAD2DEG;
    var sqrt$1 = Math.sqrt;
    var atan2 = Math.atan2;
    var round$4 = Math.round;

    var lab2lch = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$n(args, 'lab');
        var l = ref[0];
        var a = ref[1];
        var b = ref[2];
        var c = sqrt$1(a * a + b * b);
        var h = (atan2(b, a) * RAD2DEG + 360) % 360;
        if (round$4(c*10000) === 0) { h = Number.NaN; }
        return [l, c, h];
    };

    var lab2lch_1 = lab2lch;

    var unpack$o = utils.unpack;



    var rgb2lch = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$o(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        var ref$1 = rgb2lab_1(r,g,b);
        var l = ref$1[0];
        var a = ref$1[1];
        var b_ = ref$1[2];
        return lab2lch_1(l,a,b_);
    };

    var rgb2lch_1 = rgb2lch;

    var unpack$p = utils.unpack;
    var DEG2RAD = utils.DEG2RAD;
    var sin = Math.sin;
    var cos$1 = Math.cos;

    var lch2lab = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        /*
        Convert from a qualitative parameter h and a quantitative parameter l to a 24-bit pixel.
        These formulas were invented by David Dalrymple to obtain maximum contrast without going
        out of gamut if the parameters are in the range 0-1.

        A saturation multiplier was added by Gregor Aisch
        */
        var ref = unpack$p(args, 'lch');
        var l = ref[0];
        var c = ref[1];
        var h = ref[2];
        if (isNaN(h)) { h = 0; }
        h = h * DEG2RAD;
        return [l, cos$1(h) * c, sin(h) * c]
    };

    var lch2lab_1 = lch2lab;

    var unpack$q = utils.unpack;



    var lch2rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        args = unpack$q(args, 'lch');
        var l = args[0];
        var c = args[1];
        var h = args[2];
        var ref = lch2lab_1 (l,c,h);
        var L = ref[0];
        var a = ref[1];
        var b_ = ref[2];
        var ref$1 = lab2rgb_1 (L,a,b_);
        var r = ref$1[0];
        var g = ref$1[1];
        var b = ref$1[2];
        return [r, g, b, args.length > 3 ? args[3] : 1];
    };

    var lch2rgb_1 = lch2rgb;

    var unpack$r = utils.unpack;


    var hcl2rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var hcl = unpack$r(args, 'hcl').reverse();
        return lch2rgb_1.apply(void 0, hcl);
    };

    var hcl2rgb_1 = hcl2rgb;

    var unpack$s = utils.unpack;
    var type$a = utils.type;






    Color_1.prototype.lch = function() { return rgb2lch_1(this._rgb); };
    Color_1.prototype.hcl = function() { return rgb2lch_1(this._rgb).reverse(); };

    chroma_1.lch = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['lch']) ));
    };
    chroma_1.hcl = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['hcl']) ));
    };

    input.format.lch = lch2rgb_1;
    input.format.hcl = hcl2rgb_1;

    ['lch','hcl'].forEach(function (m) { return input.autodetect.push({
        p: 2,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$s(args, m);
            if (type$a(args) === 'array' && args.length === 3) {
                return m;
            }
        }
    }); });

    /**
    	X11 color names

    	http://www.w3.org/TR/css3-color/#svg-color
    */

    var w3cx11 = {
        aliceblue: '#f0f8ff',
        antiquewhite: '#faebd7',
        aqua: '#00ffff',
        aquamarine: '#7fffd4',
        azure: '#f0ffff',
        beige: '#f5f5dc',
        bisque: '#ffe4c4',
        black: '#000000',
        blanchedalmond: '#ffebcd',
        blue: '#0000ff',
        blueviolet: '#8a2be2',
        brown: '#a52a2a',
        burlywood: '#deb887',
        cadetblue: '#5f9ea0',
        chartreuse: '#7fff00',
        chocolate: '#d2691e',
        coral: '#ff7f50',
        cornflower: '#6495ed',
        cornflowerblue: '#6495ed',
        cornsilk: '#fff8dc',
        crimson: '#dc143c',
        cyan: '#00ffff',
        darkblue: '#00008b',
        darkcyan: '#008b8b',
        darkgoldenrod: '#b8860b',
        darkgray: '#a9a9a9',
        darkgreen: '#006400',
        darkgrey: '#a9a9a9',
        darkkhaki: '#bdb76b',
        darkmagenta: '#8b008b',
        darkolivegreen: '#556b2f',
        darkorange: '#ff8c00',
        darkorchid: '#9932cc',
        darkred: '#8b0000',
        darksalmon: '#e9967a',
        darkseagreen: '#8fbc8f',
        darkslateblue: '#483d8b',
        darkslategray: '#2f4f4f',
        darkslategrey: '#2f4f4f',
        darkturquoise: '#00ced1',
        darkviolet: '#9400d3',
        deeppink: '#ff1493',
        deepskyblue: '#00bfff',
        dimgray: '#696969',
        dimgrey: '#696969',
        dodgerblue: '#1e90ff',
        firebrick: '#b22222',
        floralwhite: '#fffaf0',
        forestgreen: '#228b22',
        fuchsia: '#ff00ff',
        gainsboro: '#dcdcdc',
        ghostwhite: '#f8f8ff',
        gold: '#ffd700',
        goldenrod: '#daa520',
        gray: '#808080',
        green: '#008000',
        greenyellow: '#adff2f',
        grey: '#808080',
        honeydew: '#f0fff0',
        hotpink: '#ff69b4',
        indianred: '#cd5c5c',
        indigo: '#4b0082',
        ivory: '#fffff0',
        khaki: '#f0e68c',
        laserlemon: '#ffff54',
        lavender: '#e6e6fa',
        lavenderblush: '#fff0f5',
        lawngreen: '#7cfc00',
        lemonchiffon: '#fffacd',
        lightblue: '#add8e6',
        lightcoral: '#f08080',
        lightcyan: '#e0ffff',
        lightgoldenrod: '#fafad2',
        lightgoldenrodyellow: '#fafad2',
        lightgray: '#d3d3d3',
        lightgreen: '#90ee90',
        lightgrey: '#d3d3d3',
        lightpink: '#ffb6c1',
        lightsalmon: '#ffa07a',
        lightseagreen: '#20b2aa',
        lightskyblue: '#87cefa',
        lightslategray: '#778899',
        lightslategrey: '#778899',
        lightsteelblue: '#b0c4de',
        lightyellow: '#ffffe0',
        lime: '#00ff00',
        limegreen: '#32cd32',
        linen: '#faf0e6',
        magenta: '#ff00ff',
        maroon: '#800000',
        maroon2: '#7f0000',
        maroon3: '#b03060',
        mediumaquamarine: '#66cdaa',
        mediumblue: '#0000cd',
        mediumorchid: '#ba55d3',
        mediumpurple: '#9370db',
        mediumseagreen: '#3cb371',
        mediumslateblue: '#7b68ee',
        mediumspringgreen: '#00fa9a',
        mediumturquoise: '#48d1cc',
        mediumvioletred: '#c71585',
        midnightblue: '#191970',
        mintcream: '#f5fffa',
        mistyrose: '#ffe4e1',
        moccasin: '#ffe4b5',
        navajowhite: '#ffdead',
        navy: '#000080',
        oldlace: '#fdf5e6',
        olive: '#808000',
        olivedrab: '#6b8e23',
        orange: '#ffa500',
        orangered: '#ff4500',
        orchid: '#da70d6',
        palegoldenrod: '#eee8aa',
        palegreen: '#98fb98',
        paleturquoise: '#afeeee',
        palevioletred: '#db7093',
        papayawhip: '#ffefd5',
        peachpuff: '#ffdab9',
        peru: '#cd853f',
        pink: '#ffc0cb',
        plum: '#dda0dd',
        powderblue: '#b0e0e6',
        purple: '#800080',
        purple2: '#7f007f',
        purple3: '#a020f0',
        rebeccapurple: '#663399',
        red: '#ff0000',
        rosybrown: '#bc8f8f',
        royalblue: '#4169e1',
        saddlebrown: '#8b4513',
        salmon: '#fa8072',
        sandybrown: '#f4a460',
        seagreen: '#2e8b57',
        seashell: '#fff5ee',
        sienna: '#a0522d',
        silver: '#c0c0c0',
        skyblue: '#87ceeb',
        slateblue: '#6a5acd',
        slategray: '#708090',
        slategrey: '#708090',
        snow: '#fffafa',
        springgreen: '#00ff7f',
        steelblue: '#4682b4',
        tan: '#d2b48c',
        teal: '#008080',
        thistle: '#d8bfd8',
        tomato: '#ff6347',
        turquoise: '#40e0d0',
        violet: '#ee82ee',
        wheat: '#f5deb3',
        white: '#ffffff',
        whitesmoke: '#f5f5f5',
        yellow: '#ffff00',
        yellowgreen: '#9acd32'
    };

    var w3cx11_1 = w3cx11;

    var type$b = utils.type;





    Color_1.prototype.name = function() {
        var hex = rgb2hex_1(this._rgb, 'rgb');
        for (var i = 0, list = Object.keys(w3cx11_1); i < list.length; i += 1) {
            var n = list[i];

            if (w3cx11_1[n] === hex) { return n.toLowerCase(); }
        }
        return hex;
    };

    input.format.named = function (name) {
        name = name.toLowerCase();
        if (w3cx11_1[name]) { return hex2rgb_1(w3cx11_1[name]); }
        throw new Error('unknown color name: '+name);
    };

    input.autodetect.push({
        p: 5,
        test: function (h) {
            var rest = [], len = arguments.length - 1;
            while ( len-- > 0 ) rest[ len ] = arguments[ len + 1 ];

            if (!rest.length && type$b(h) === 'string' && w3cx11_1[h.toLowerCase()]) {
                return 'named';
            }
        }
    });

    var unpack$t = utils.unpack;

    var rgb2num = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var ref = unpack$t(args, 'rgb');
        var r = ref[0];
        var g = ref[1];
        var b = ref[2];
        return (r << 16) + (g << 8) + b;
    };

    var rgb2num_1 = rgb2num;

    var type$c = utils.type;

    var num2rgb = function (num) {
        if (type$c(num) == "number" && num >= 0 && num <= 0xFFFFFF) {
            var r = num >> 16;
            var g = (num >> 8) & 0xFF;
            var b = num & 0xFF;
            return [r,g,b,1];
        }
        throw new Error("unknown num color: "+num);
    };

    var num2rgb_1 = num2rgb;

    var type$d = utils.type;



    Color_1.prototype.num = function() {
        return rgb2num_1(this._rgb);
    };

    chroma_1.num = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['num']) ));
    };

    input.format.num = num2rgb_1;

    input.autodetect.push({
        p: 5,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            if (args.length === 1 && type$d(args[0]) === 'number' && args[0] >= 0 && args[0] <= 0xFFFFFF) {
                return 'num';
            }
        }
    });

    var unpack$u = utils.unpack;
    var type$e = utils.type;
    var round$5 = Math.round;

    Color_1.prototype.rgb = function(rnd) {
        if ( rnd === void 0 ) rnd=true;

        if (rnd === false) { return this._rgb.slice(0,3); }
        return this._rgb.slice(0,3).map(round$5);
    };

    Color_1.prototype.rgba = function(rnd) {
        if ( rnd === void 0 ) rnd=true;

        return this._rgb.slice(0,4).map(function (v,i) {
            return i<3 ? (rnd === false ? v : round$5(v)) : v;
        });
    };

    chroma_1.rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['rgb']) ));
    };

    input.format.rgb = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var rgba = unpack$u(args, 'rgba');
        if (rgba[3] === undefined) { rgba[3] = 1; }
        return rgba;
    };

    input.autodetect.push({
        p: 3,
        test: function () {
            var args = [], len = arguments.length;
            while ( len-- ) args[ len ] = arguments[ len ];

            args = unpack$u(args, 'rgba');
            if (type$e(args) === 'array' && (args.length === 3 ||
                args.length === 4 && type$e(args[3]) == 'number' && args[3] >= 0 && args[3] <= 1)) {
                return 'rgb';
            }
        }
    });

    /*
     * Based on implementation by Neil Bartlett
     * https://github.com/neilbartlett/color-temperature
     */

    var log = Math.log;

    var temperature2rgb = function (kelvin) {
        var temp = kelvin / 100;
        var r,g,b;
        if (temp < 66) {
            r = 255;
            g = -155.25485562709179 - 0.44596950469579133 * (g = temp-2) + 104.49216199393888 * log(g);
            b = temp < 20 ? 0 : -254.76935184120902 + 0.8274096064007395 * (b = temp-10) + 115.67994401066147 * log(b);
        } else {
            r = 351.97690566805693 + 0.114206453784165 * (r = temp-55) - 40.25366309332127 * log(r);
            g = 325.4494125711974 + 0.07943456536662342 * (g = temp-50) - 28.0852963507957 * log(g);
            b = 255;
        }
        return [r,g,b,1];
    };

    var temperature2rgb_1 = temperature2rgb;

    /*
     * Based on implementation by Neil Bartlett
     * https://github.com/neilbartlett/color-temperature
     **/


    var unpack$v = utils.unpack;
    var round$6 = Math.round;

    var rgb2temperature = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        var rgb = unpack$v(args, 'rgb');
        var r = rgb[0], b = rgb[2];
        var minTemp = 1000;
        var maxTemp = 40000;
        var eps = 0.4;
        var temp;
        while (maxTemp - minTemp > eps) {
            temp = (maxTemp + minTemp) * 0.5;
            var rgb$1 = temperature2rgb_1(temp);
            if ((rgb$1[2] / rgb$1[0]) >= (b / r)) {
                maxTemp = temp;
            } else {
                minTemp = temp;
            }
        }
        return round$6(temp);
    };

    var rgb2temperature_1 = rgb2temperature;

    Color_1.prototype.temp =
    Color_1.prototype.kelvin =
    Color_1.prototype.temperature = function() {
        return rgb2temperature_1(this._rgb);
    };

    chroma_1.temp =
    chroma_1.kelvin =
    chroma_1.temperature = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        return new (Function.prototype.bind.apply( Color_1, [ null ].concat( args, ['temp']) ));
    };

    input.format.temp =
    input.format.kelvin =
    input.format.temperature = temperature2rgb_1;

    var type$f = utils.type;

    Color_1.prototype.alpha = function(a, mutate) {
        if ( mutate === void 0 ) mutate=false;

        if (a !== undefined && type$f(a) === 'number') {
            if (mutate) {
                this._rgb[3] = a;
                return this;
            }
            return new Color_1([this._rgb[0], this._rgb[1], this._rgb[2], a], 'rgb');
        }
        return this._rgb[3];
    };

    Color_1.prototype.clipped = function() {
        return this._rgb._clipped || false;
    };

    Color_1.prototype.darken = function(amount) {
    	if ( amount === void 0 ) amount=1;

    	var me = this;
    	var lab = me.lab();
    	lab[0] -= labConstants.Kn * amount;
    	return new Color_1(lab, 'lab').alpha(me.alpha(), true);
    };

    Color_1.prototype.brighten = function(amount) {
    	if ( amount === void 0 ) amount=1;

    	return this.darken(-amount);
    };

    Color_1.prototype.darker = Color_1.prototype.darken;
    Color_1.prototype.brighter = Color_1.prototype.brighten;

    Color_1.prototype.get = function(mc) {
        var ref = mc.split('.');
        var mode = ref[0];
        var channel = ref[1];
        var src = this[mode]();
        if (channel) {
            var i = mode.indexOf(channel);
            if (i > -1) { return src[i]; }
            throw new Error(("unknown channel " + channel + " in mode " + mode));
        } else {
            return src;
        }
    };

    var type$g = utils.type;
    var pow$2 = Math.pow;

    var EPS = 1e-7;
    var MAX_ITER = 20;

    Color_1.prototype.luminance = function(lum) {
        if (lum !== undefined && type$g(lum) === 'number') {
            if (lum === 0) {
                // return pure black
                return new Color_1([0,0,0,this._rgb[3]], 'rgb');
            }
            if (lum === 1) {
                // return pure white
                return new Color_1([255,255,255,this._rgb[3]], 'rgb');
            }
            // compute new color using...
            var cur_lum = this.luminance();
            var mode = 'rgb';
            var max_iter = MAX_ITER;

            var test = function (low, high) {
                var mid = low.interpolate(high, 0.5, mode);
                var lm = mid.luminance();
                if (Math.abs(lum - lm) < EPS || !max_iter--) {
                    // close enough
                    return mid;
                }
                return lm > lum ? test(low, mid) : test(mid, high);
            };

            var rgb = (cur_lum > lum ? test(new Color_1([0,0,0]), this) : test(this, new Color_1([255,255,255]))).rgb();
            return new Color_1(rgb.concat( [this._rgb[3]]));
        }
        return rgb2luminance.apply(void 0, (this._rgb).slice(0,3));
    };


    var rgb2luminance = function (r,g,b) {
        // relative luminance
        // see http://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
        r = luminance_x(r);
        g = luminance_x(g);
        b = luminance_x(b);
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    };

    var luminance_x = function (x) {
        x /= 255;
        return x <= 0.03928 ? x/12.92 : pow$2((x+0.055)/1.055, 2.4);
    };

    var interpolator = {};

    var type$h = utils.type;


    var mix = function (col1, col2, f) {
        if ( f === void 0 ) f=0.5;
        var rest = [], len = arguments.length - 3;
        while ( len-- > 0 ) rest[ len ] = arguments[ len + 3 ];

        var mode = rest[0] || 'lrgb';
        if (!interpolator[mode] && !rest.length) {
            // fall back to the first supported mode
            mode = Object.keys(interpolator)[0];
        }
        if (!interpolator[mode]) {
            throw new Error(("interpolation mode " + mode + " is not defined"));
        }
        if (type$h(col1) !== 'object') { col1 = new Color_1(col1); }
        if (type$h(col2) !== 'object') { col2 = new Color_1(col2); }
        return interpolator[mode](col1, col2, f)
            .alpha(col1.alpha() + f * (col2.alpha() - col1.alpha()));
    };

    Color_1.prototype.mix =
    Color_1.prototype.interpolate = function(col2, f) {
    	if ( f === void 0 ) f=0.5;
    	var rest = [], len = arguments.length - 2;
    	while ( len-- > 0 ) rest[ len ] = arguments[ len + 2 ];

    	return mix.apply(void 0, [ this, col2, f ].concat( rest ));
    };

    Color_1.prototype.premultiply = function(mutate) {
    	if ( mutate === void 0 ) mutate=false;

    	var rgb = this._rgb;
    	var a = rgb[3];
    	if (mutate) {
    		this._rgb = [rgb[0]*a, rgb[1]*a, rgb[2]*a, a];
    		return this;
    	} else {
    		return new Color_1([rgb[0]*a, rgb[1]*a, rgb[2]*a, a], 'rgb');
    	}
    };

    Color_1.prototype.saturate = function(amount) {
    	if ( amount === void 0 ) amount=1;

    	var me = this;
    	var lch = me.lch();
    	lch[1] += labConstants.Kn * amount;
    	if (lch[1] < 0) { lch[1] = 0; }
    	return new Color_1(lch, 'lch').alpha(me.alpha(), true);
    };

    Color_1.prototype.desaturate = function(amount) {
    	if ( amount === void 0 ) amount=1;

    	return this.saturate(-amount);
    };

    var type$i = utils.type;

    Color_1.prototype.set = function(mc, value, mutate) {
        if ( mutate === void 0 ) mutate=false;

        var ref = mc.split('.');
        var mode = ref[0];
        var channel = ref[1];
        var src = this[mode]();
        if (channel) {
            var i = mode.indexOf(channel);
            if (i > -1) {
                if (type$i(value) == 'string') {
                    switch(value.charAt(0)) {
                        case '+': src[i] += +value; break;
                        case '-': src[i] += +value; break;
                        case '*': src[i] *= +(value.substr(1)); break;
                        case '/': src[i] /= +(value.substr(1)); break;
                        default: src[i] = +value;
                    }
                } else if (type$i(value) === 'number') {
                    src[i] = value;
                } else {
                    throw new Error("unsupported value for Color.set");
                }
                var out = new Color_1(src, mode);
                if (mutate) {
                    this._rgb = out._rgb;
                    return this;
                }
                return out;
            }
            throw new Error(("unknown channel " + channel + " in mode " + mode));
        } else {
            return src;
        }
    };

    var rgb$1 = function (col1, col2, f) {
        var xyz0 = col1._rgb;
        var xyz1 = col2._rgb;
        return new Color_1(
            xyz0[0] + f * (xyz1[0]-xyz0[0]),
            xyz0[1] + f * (xyz1[1]-xyz0[1]),
            xyz0[2] + f * (xyz1[2]-xyz0[2]),
            'rgb'
        )
    };

    // register interpolator
    interpolator.rgb = rgb$1;

    var sqrt$2 = Math.sqrt;
    var pow$3 = Math.pow;

    var lrgb = function (col1, col2, f) {
        var ref = col1._rgb;
        var x1 = ref[0];
        var y1 = ref[1];
        var z1 = ref[2];
        var ref$1 = col2._rgb;
        var x2 = ref$1[0];
        var y2 = ref$1[1];
        var z2 = ref$1[2];
        return new Color_1(
            sqrt$2(pow$3(x1,2) * (1-f) + pow$3(x2,2) * f),
            sqrt$2(pow$3(y1,2) * (1-f) + pow$3(y2,2) * f),
            sqrt$2(pow$3(z1,2) * (1-f) + pow$3(z2,2) * f),
            'rgb'
        )
    };

    // register interpolator
    interpolator.lrgb = lrgb;

    var lab$1 = function (col1, col2, f) {
        var xyz0 = col1.lab();
        var xyz1 = col2.lab();
        return new Color_1(
            xyz0[0] + f * (xyz1[0]-xyz0[0]),
            xyz0[1] + f * (xyz1[1]-xyz0[1]),
            xyz0[2] + f * (xyz1[2]-xyz0[2]),
            'lab'
        )
    };

    // register interpolator
    interpolator.lab = lab$1;

    var _hsx = function (col1, col2, f, m) {
        var assign, assign$1;

        var xyz0, xyz1;
        if (m === 'hsl') {
            xyz0 = col1.hsl();
            xyz1 = col2.hsl();
        } else if (m === 'hsv') {
            xyz0 = col1.hsv();
            xyz1 = col2.hsv();
        } else if (m === 'hcg') {
            xyz0 = col1.hcg();
            xyz1 = col2.hcg();
        } else if (m === 'hsi') {
            xyz0 = col1.hsi();
            xyz1 = col2.hsi();
        } else if (m === 'lch' || m === 'hcl') {
            m = 'hcl';
            xyz0 = col1.hcl();
            xyz1 = col2.hcl();
        }

        var hue0, hue1, sat0, sat1, lbv0, lbv1;
        if (m.substr(0, 1) === 'h') {
            (assign = xyz0, hue0 = assign[0], sat0 = assign[1], lbv0 = assign[2]);
            (assign$1 = xyz1, hue1 = assign$1[0], sat1 = assign$1[1], lbv1 = assign$1[2]);
        }

        var sat, hue, lbv, dh;

        if (!isNaN(hue0) && !isNaN(hue1)) {
            // both colors have hue
            if (hue1 > hue0 && hue1 - hue0 > 180) {
                dh = hue1-(hue0+360);
            } else if (hue1 < hue0 && hue0 - hue1 > 180) {
                dh = hue1+360-hue0;
            } else{
                dh = hue1 - hue0;
            }
            hue = hue0 + f * dh;
        } else if (!isNaN(hue0)) {
            hue = hue0;
            if ((lbv1 == 1 || lbv1 == 0) && m != 'hsv') { sat = sat0; }
        } else if (!isNaN(hue1)) {
            hue = hue1;
            if ((lbv0 == 1 || lbv0 == 0) && m != 'hsv') { sat = sat1; }
        } else {
            hue = Number.NaN;
        }

        if (sat === undefined) { sat = sat0 + f * (sat1 - sat0); }
        lbv = lbv0 + f * (lbv1-lbv0);
        return new Color_1([hue, sat, lbv], m);
    };

    var lch$1 = function (col1, col2, f) {
    	return _hsx(col1, col2, f, 'lch');
    };

    // register interpolator
    interpolator.lch = lch$1;
    interpolator.hcl = lch$1;

    var num$1 = function (col1, col2, f) {
        var c1 = col1.num();
        var c2 = col2.num();
        return new Color_1(c1 + f * (c2-c1), 'num')
    };

    // register interpolator
    interpolator.num = num$1;

    var hcg$1 = function (col1, col2, f) {
    	return _hsx(col1, col2, f, 'hcg');
    };

    // register interpolator
    interpolator.hcg = hcg$1;

    var hsi$1 = function (col1, col2, f) {
    	return _hsx(col1, col2, f, 'hsi');
    };

    // register interpolator
    interpolator.hsi = hsi$1;

    var hsl$1 = function (col1, col2, f) {
    	return _hsx(col1, col2, f, 'hsl');
    };

    // register interpolator
    interpolator.hsl = hsl$1;

    var hsv$1 = function (col1, col2, f) {
    	return _hsx(col1, col2, f, 'hsv');
    };

    // register interpolator
    interpolator.hsv = hsv$1;

    var clip_rgb$2 = utils.clip_rgb;
    var pow$4 = Math.pow;
    var sqrt$3 = Math.sqrt;
    var PI$1 = Math.PI;
    var cos$2 = Math.cos;
    var sin$1 = Math.sin;
    var atan2$1 = Math.atan2;

    var average = function (colors, mode, weights) {
        if ( mode === void 0 ) mode='lrgb';
        if ( weights === void 0 ) weights=null;

        var l = colors.length;
        if (!weights) { weights = Array.from(new Array(l)).map(function () { return 1; }); }
        // normalize weights
        var k = l / weights.reduce(function(a, b) { return a + b; });
        weights.forEach(function (w,i) { weights[i] *= k; });
        // convert colors to Color objects
        colors = colors.map(function (c) { return new Color_1(c); });
        if (mode === 'lrgb') {
            return _average_lrgb(colors, weights)
        }
        var first = colors.shift();
        var xyz = first.get(mode);
        var cnt = [];
        var dx = 0;
        var dy = 0;
        // initial color
        for (var i=0; i<xyz.length; i++) {
            xyz[i] = (xyz[i] || 0) * weights[0];
            cnt.push(isNaN(xyz[i]) ? 0 : weights[0]);
            if (mode.charAt(i) === 'h' && !isNaN(xyz[i])) {
                var A = xyz[i] / 180 * PI$1;
                dx += cos$2(A) * weights[0];
                dy += sin$1(A) * weights[0];
            }
        }

        var alpha = first.alpha() * weights[0];
        colors.forEach(function (c,ci) {
            var xyz2 = c.get(mode);
            alpha += c.alpha() * weights[ci+1];
            for (var i=0; i<xyz.length; i++) {
                if (!isNaN(xyz2[i])) {
                    cnt[i] += weights[ci+1];
                    if (mode.charAt(i) === 'h') {
                        var A = xyz2[i] / 180 * PI$1;
                        dx += cos$2(A) * weights[ci+1];
                        dy += sin$1(A) * weights[ci+1];
                    } else {
                        xyz[i] += xyz2[i] * weights[ci+1];
                    }
                }
            }
        });

        for (var i$1=0; i$1<xyz.length; i$1++) {
            if (mode.charAt(i$1) === 'h') {
                var A$1 = atan2$1(dy / cnt[i$1], dx / cnt[i$1]) / PI$1 * 180;
                while (A$1 < 0) { A$1 += 360; }
                while (A$1 >= 360) { A$1 -= 360; }
                xyz[i$1] = A$1;
            } else {
                xyz[i$1] = xyz[i$1]/cnt[i$1];
            }
        }
        alpha /= l;
        return (new Color_1(xyz, mode)).alpha(alpha > 0.99999 ? 1 : alpha, true);
    };


    var _average_lrgb = function (colors, weights) {
        var l = colors.length;
        var xyz = [0,0,0,0];
        for (var i=0; i < colors.length; i++) {
            var col = colors[i];
            var f = weights[i] / l;
            var rgb = col._rgb;
            xyz[0] += pow$4(rgb[0],2) * f;
            xyz[1] += pow$4(rgb[1],2) * f;
            xyz[2] += pow$4(rgb[2],2) * f;
            xyz[3] += rgb[3] * f;
        }
        xyz[0] = sqrt$3(xyz[0]);
        xyz[1] = sqrt$3(xyz[1]);
        xyz[2] = sqrt$3(xyz[2]);
        if (xyz[3] > 0.9999999) { xyz[3] = 1; }
        return new Color_1(clip_rgb$2(xyz));
    };

    // minimal multi-purpose interface

    // @requires utils color analyze


    var type$j = utils.type;

    var pow$5 = Math.pow;

    var scale = function(colors) {

        // constructor
        var _mode = 'rgb';
        var _nacol = chroma_1('#ccc');
        var _spread = 0;
        // const _fixed = false;
        var _domain = [0, 1];
        var _pos = [];
        var _padding = [0,0];
        var _classes = false;
        var _colors = [];
        var _out = false;
        var _min = 0;
        var _max = 1;
        var _correctLightness = false;
        var _colorCache = {};
        var _useCache = true;
        var _gamma = 1;

        // private methods

        var setColors = function(colors) {
            colors = colors || ['#fff', '#000'];
            if (colors && type$j(colors) === 'string' && chroma_1.brewer &&
                chroma_1.brewer[colors.toLowerCase()]) {
                colors = chroma_1.brewer[colors.toLowerCase()];
            }
            if (type$j(colors) === 'array') {
                // handle single color
                if (colors.length === 1) {
                    colors = [colors[0], colors[0]];
                }
                // make a copy of the colors
                colors = colors.slice(0);
                // convert to chroma classes
                for (var c=0; c<colors.length; c++) {
                    colors[c] = chroma_1(colors[c]);
                }
                // auto-fill color position
                _pos.length = 0;
                for (var c$1=0; c$1<colors.length; c$1++) {
                    _pos.push(c$1/(colors.length-1));
                }
            }
            resetCache();
            return _colors = colors;
        };

        var getClass = function(value) {
            if (_classes != null) {
                var n = _classes.length-1;
                var i = 0;
                while (i < n && value >= _classes[i]) {
                    i++;
                }
                return i-1;
            }
            return 0;
        };

        var tMapLightness = function (t) { return t; };
        var tMapDomain = function (t) { return t; };

        // const classifyValue = function(value) {
        //     let val = value;
        //     if (_classes.length > 2) {
        //         const n = _classes.length-1;
        //         const i = getClass(value);
        //         const minc = _classes[0] + ((_classes[1]-_classes[0]) * (0 + (_spread * 0.5)));  // center of 1st class
        //         const maxc = _classes[n-1] + ((_classes[n]-_classes[n-1]) * (1 - (_spread * 0.5)));  // center of last class
        //         val = _min + ((((_classes[i] + ((_classes[i+1] - _classes[i]) * 0.5)) - minc) / (maxc-minc)) * (_max - _min));
        //     }
        //     return val;
        // };

        var getColor = function(val, bypassMap) {
            var col, t;
            if (bypassMap == null) { bypassMap = false; }
            if (isNaN(val) || (val === null)) { return _nacol; }
            if (!bypassMap) {
                if (_classes && (_classes.length > 2)) {
                    // find the class
                    var c = getClass(val);
                    t = c / (_classes.length-2);
                } else if (_max !== _min) {
                    // just interpolate between min/max
                    t = (val - _min) / (_max - _min);
                } else {
                    t = 1;
                }
            } else {
                t = val;
            }

            // domain map
            t = tMapDomain(t);

            if (!bypassMap) {
                t = tMapLightness(t);  // lightness correction
            }

            if (_gamma !== 1) { t = pow$5(t, _gamma); }

            t = _padding[0] + (t * (1 - _padding[0] - _padding[1]));

            t = Math.min(1, Math.max(0, t));

            var k = Math.floor(t * 10000);

            if (_useCache && _colorCache[k]) {
                col = _colorCache[k];
            } else {
                if (type$j(_colors) === 'array') {
                    //for i in [0.._pos.length-1]
                    for (var i=0; i<_pos.length; i++) {
                        var p = _pos[i];
                        if (t <= p) {
                            col = _colors[i];
                            break;
                        }
                        if ((t >= p) && (i === (_pos.length-1))) {
                            col = _colors[i];
                            break;
                        }
                        if (t > p && t < _pos[i+1]) {
                            t = (t-p)/(_pos[i+1]-p);
                            col = chroma_1.interpolate(_colors[i], _colors[i+1], t, _mode);
                            break;
                        }
                    }
                } else if (type$j(_colors) === 'function') {
                    col = _colors(t);
                }
                if (_useCache) { _colorCache[k] = col; }
            }
            return col;
        };

        var resetCache = function () { return _colorCache = {}; };

        setColors(colors);

        // public interface

        var f = function(v) {
            var c = chroma_1(getColor(v));
            if (_out && c[_out]) { return c[_out](); } else { return c; }
        };

        f.classes = function(classes) {
            if (classes != null) {
                if (type$j(classes) === 'array') {
                    _classes = classes;
                    _domain = [classes[0], classes[classes.length-1]];
                } else {
                    var d = chroma_1.analyze(_domain);
                    if (classes === 0) {
                        _classes = [d.min, d.max];
                    } else {
                        _classes = chroma_1.limits(d, 'e', classes);
                    }
                }
                return f;
            }
            return _classes;
        };


        f.domain = function(domain) {
            if (!arguments.length) {
                return _domain;
            }
            _min = domain[0];
            _max = domain[domain.length-1];
            _pos = [];
            var k = _colors.length;
            if ((domain.length === k) && (_min !== _max)) {
                // update positions
                for (var i = 0, list = Array.from(domain); i < list.length; i += 1) {
                    var d = list[i];

                  _pos.push((d-_min) / (_max-_min));
                }
            } else {
                for (var c=0; c<k; c++) {
                    _pos.push(c/(k-1));
                }
                if (domain.length > 2) {
                    // set domain map
                    var tOut = domain.map(function (d,i) { return i/(domain.length-1); });
                    var tBreaks = domain.map(function (d) { return (d - _min) / (_max - _min); });
                    if (!tBreaks.every(function (val, i) { return tOut[i] === val; })) {
                        tMapDomain = function (t) {
                            if (t <= 0 || t >= 1) { return t; }
                            var i = 0;
                            while (t >= tBreaks[i+1]) { i++; }
                            var f = (t - tBreaks[i]) / (tBreaks[i+1] - tBreaks[i]);
                            var out = tOut[i] + f * (tOut[i+1] - tOut[i]);
                            return out;
                        };
                    }

                }
            }
            _domain = [_min, _max];
            return f;
        };

        f.mode = function(_m) {
            if (!arguments.length) {
                return _mode;
            }
            _mode = _m;
            resetCache();
            return f;
        };

        f.range = function(colors, _pos) {
            setColors(colors, _pos);
            return f;
        };

        f.out = function(_o) {
            _out = _o;
            return f;
        };

        f.spread = function(val) {
            if (!arguments.length) {
                return _spread;
            }
            _spread = val;
            return f;
        };

        f.correctLightness = function(v) {
            if (v == null) { v = true; }
            _correctLightness = v;
            resetCache();
            if (_correctLightness) {
                tMapLightness = function(t) {
                    var L0 = getColor(0, true).lab()[0];
                    var L1 = getColor(1, true).lab()[0];
                    var pol = L0 > L1;
                    var L_actual = getColor(t, true).lab()[0];
                    var L_ideal = L0 + ((L1 - L0) * t);
                    var L_diff = L_actual - L_ideal;
                    var t0 = 0;
                    var t1 = 1;
                    var max_iter = 20;
                    while ((Math.abs(L_diff) > 1e-2) && (max_iter-- > 0)) {
                        (function() {
                            if (pol) { L_diff *= -1; }
                            if (L_diff < 0) {
                                t0 = t;
                                t += (t1 - t) * 0.5;
                            } else {
                                t1 = t;
                                t += (t0 - t) * 0.5;
                            }
                            L_actual = getColor(t, true).lab()[0];
                            return L_diff = L_actual - L_ideal;
                        })();
                    }
                    return t;
                };
            } else {
                tMapLightness = function (t) { return t; };
            }
            return f;
        };

        f.padding = function(p) {
            if (p != null) {
                if (type$j(p) === 'number') {
                    p = [p,p];
                }
                _padding = p;
                return f;
            } else {
                return _padding;
            }
        };

        f.colors = function(numColors, out) {
            // If no arguments are given, return the original colors that were provided
            if (arguments.length < 2) { out = 'hex'; }
            var result = [];

            if (arguments.length === 0) {
                result = _colors.slice(0);

            } else if (numColors === 1) {
                result = [f(0.5)];

            } else if (numColors > 1) {
                var dm = _domain[0];
                var dd = _domain[1] - dm;
                result = __range__(0, numColors, false).map(function (i) { return f( dm + ((i/(numColors-1)) * dd) ); });

            } else { // returns all colors based on the defined classes
                colors = [];
                var samples = [];
                if (_classes && (_classes.length > 2)) {
                    for (var i = 1, end = _classes.length, asc = 1 <= end; asc ? i < end : i > end; asc ? i++ : i--) {
                        samples.push((_classes[i-1]+_classes[i])*0.5);
                    }
                } else {
                    samples = _domain;
                }
                result = samples.map(function (v) { return f(v); });
            }

            if (chroma_1[out]) {
                result = result.map(function (c) { return c[out](); });
            }
            return result;
        };

        f.cache = function(c) {
            if (c != null) {
                _useCache = c;
                return f;
            } else {
                return _useCache;
            }
        };

        f.gamma = function(g) {
            if (g != null) {
                _gamma = g;
                return f;
            } else {
                return _gamma;
            }
        };

        f.nodata = function(d) {
            if (d != null) {
                _nacol = chroma_1(d);
                return f;
            } else {
                return _nacol;
            }
        };

        return f;
    };

    function __range__(left, right, inclusive) {
      var range = [];
      var ascending = left < right;
      var end = !inclusive ? right : ascending ? right + 1 : right - 1;
      for (var i = left; ascending ? i < end : i > end; ascending ? i++ : i--) {
        range.push(i);
      }
      return range;
    }

    //
    // interpolates between a set of colors uzing a bezier spline
    //

    // @requires utils lab




    var bezier = function(colors) {
        var assign, assign$1, assign$2;

        var I, lab0, lab1, lab2;
        colors = colors.map(function (c) { return new Color_1(c); });
        if (colors.length === 2) {
            // linear interpolation
            (assign = colors.map(function (c) { return c.lab(); }), lab0 = assign[0], lab1 = assign[1]);
            I = function(t) {
                var lab = ([0, 1, 2].map(function (i) { return lab0[i] + (t * (lab1[i] - lab0[i])); }));
                return new Color_1(lab, 'lab');
            };
        } else if (colors.length === 3) {
            // quadratic bezier interpolation
            (assign$1 = colors.map(function (c) { return c.lab(); }), lab0 = assign$1[0], lab1 = assign$1[1], lab2 = assign$1[2]);
            I = function(t) {
                var lab = ([0, 1, 2].map(function (i) { return ((1-t)*(1-t) * lab0[i]) + (2 * (1-t) * t * lab1[i]) + (t * t * lab2[i]); }));
                return new Color_1(lab, 'lab');
            };
        } else if (colors.length === 4) {
            // cubic bezier interpolation
            var lab3;
            (assign$2 = colors.map(function (c) { return c.lab(); }), lab0 = assign$2[0], lab1 = assign$2[1], lab2 = assign$2[2], lab3 = assign$2[3]);
            I = function(t) {
                var lab = ([0, 1, 2].map(function (i) { return ((1-t)*(1-t)*(1-t) * lab0[i]) + (3 * (1-t) * (1-t) * t * lab1[i]) + (3 * (1-t) * t * t * lab2[i]) + (t*t*t * lab3[i]); }));
                return new Color_1(lab, 'lab');
            };
        } else if (colors.length === 5) {
            var I0 = bezier(colors.slice(0, 3));
            var I1 = bezier(colors.slice(2, 5));
            I = function(t) {
                if (t < 0.5) {
                    return I0(t*2);
                } else {
                    return I1((t-0.5)*2);
                }
            };
        }
        return I;
    };

    var bezier_1 = function (colors) {
        var f = bezier(colors);
        f.scale = function () { return scale(f); };
        return f;
    };

    /*
     * interpolates between a set of colors uzing a bezier spline
     * blend mode formulas taken from http://www.venture-ware.com/kevin/coding/lets-learn-math-photoshop-blend-modes/
     */




    var blend = function (bottom, top, mode) {
        if (!blend[mode]) {
            throw new Error('unknown blend mode ' + mode);
        }
        return blend[mode](bottom, top);
    };

    var blend_f = function (f) { return function (bottom,top) {
            var c0 = chroma_1(top).rgb();
            var c1 = chroma_1(bottom).rgb();
            return chroma_1.rgb(f(c0, c1));
        }; };

    var each = function (f) { return function (c0, c1) {
            var out = [];
            out[0] = f(c0[0], c1[0]);
            out[1] = f(c0[1], c1[1]);
            out[2] = f(c0[2], c1[2]);
            return out;
        }; };

    var normal = function (a) { return a; };
    var multiply = function (a,b) { return a * b / 255; };
    var darken$1 = function (a,b) { return a > b ? b : a; };
    var lighten = function (a,b) { return a > b ? a : b; };
    var screen = function (a,b) { return 255 * (1 - (1-a/255) * (1-b/255)); };
    var overlay = function (a,b) { return b < 128 ? 2 * a * b / 255 : 255 * (1 - 2 * (1 - a / 255 ) * ( 1 - b / 255 )); };
    var burn = function (a,b) { return 255 * (1 - (1 - b / 255) / (a/255)); };
    var dodge = function (a,b) {
        if (a === 255) { return 255; }
        a = 255 * (b / 255) / (1 - a / 255);
        return a > 255 ? 255 : a
    };

    // # add = (a,b) ->
    // #     if (a + b > 255) then 255 else a + b

    blend.normal = blend_f(each(normal));
    blend.multiply = blend_f(each(multiply));
    blend.screen = blend_f(each(screen));
    blend.overlay = blend_f(each(overlay));
    blend.darken = blend_f(each(darken$1));
    blend.lighten = blend_f(each(lighten));
    blend.dodge = blend_f(each(dodge));
    blend.burn = blend_f(each(burn));
    // blend.add = blend_f(each(add));

    var blend_1 = blend;

    // cubehelix interpolation
    // based on D.A. Green "A colour scheme for the display of astronomical intensity images"
    // http://astron-soc.in/bulletin/11June/289392011.pdf

    var type$k = utils.type;
    var clip_rgb$3 = utils.clip_rgb;
    var TWOPI$2 = utils.TWOPI;
    var pow$6 = Math.pow;
    var sin$2 = Math.sin;
    var cos$3 = Math.cos;


    var cubehelix = function(start, rotations, hue, gamma, lightness) {
        if ( start === void 0 ) start=300;
        if ( rotations === void 0 ) rotations=-1.5;
        if ( hue === void 0 ) hue=1;
        if ( gamma === void 0 ) gamma=1;
        if ( lightness === void 0 ) lightness=[0,1];

        var dh = 0, dl;
        if (type$k(lightness) === 'array') {
            dl = lightness[1] - lightness[0];
        } else {
            dl = 0;
            lightness = [lightness, lightness];
        }

        var f = function(fract) {
            var a = TWOPI$2 * (((start+120)/360) + (rotations * fract));
            var l = pow$6(lightness[0] + (dl * fract), gamma);
            var h = dh !== 0 ? hue[0] + (fract * dh) : hue;
            var amp = (h * l * (1-l)) / 2;
            var cos_a = cos$3(a);
            var sin_a = sin$2(a);
            var r = l + (amp * ((-0.14861 * cos_a) + (1.78277* sin_a)));
            var g = l + (amp * ((-0.29227 * cos_a) - (0.90649* sin_a)));
            var b = l + (amp * (+1.97294 * cos_a));
            return chroma_1(clip_rgb$3([r*255,g*255,b*255,1]));
        };

        f.start = function(s) {
            if ((s == null)) { return start; }
            start = s;
            return f;
        };

        f.rotations = function(r) {
            if ((r == null)) { return rotations; }
            rotations = r;
            return f;
        };

        f.gamma = function(g) {
            if ((g == null)) { return gamma; }
            gamma = g;
            return f;
        };

        f.hue = function(h) {
            if ((h == null)) { return hue; }
            hue = h;
            if (type$k(hue) === 'array') {
                dh = hue[1] - hue[0];
                if (dh === 0) { hue = hue[1]; }
            } else {
                dh = 0;
            }
            return f;
        };

        f.lightness = function(h) {
            if ((h == null)) { return lightness; }
            if (type$k(h) === 'array') {
                lightness = h;
                dl = h[1] - h[0];
            } else {
                lightness = [h,h];
                dl = 0;
            }
            return f;
        };

        f.scale = function () { return chroma_1.scale(f); };

        f.hue(hue);

        return f;
    };

    var digits = '0123456789abcdef';

    var floor$2 = Math.floor;
    var random = Math.random;

    var random_1 = function () {
        var code = '#';
        for (var i=0; i<6; i++) {
            code += digits.charAt(floor$2(random() * 16));
        }
        return new Color_1(code, 'hex');
    };

    var log$1 = Math.log;
    var pow$7 = Math.pow;
    var floor$3 = Math.floor;
    var abs = Math.abs;


    var analyze = function (data, key) {
        if ( key === void 0 ) key=null;

        var r = {
            min: Number.MAX_VALUE,
            max: Number.MAX_VALUE*-1,
            sum: 0,
            values: [],
            count: 0
        };
        if (type(data) === 'object') {
            data = Object.values(data);
        }
        data.forEach(function (val) {
            if (key && type(val) === 'object') { val = val[key]; }
            if (val !== undefined && val !== null && !isNaN(val)) {
                r.values.push(val);
                r.sum += val;
                if (val < r.min) { r.min = val; }
                if (val > r.max) { r.max = val; }
                r.count += 1;
            }
        });

        r.domain = [r.min, r.max];

        r.limits = function (mode, num) { return limits(r, mode, num); };

        return r;
    };


    var limits = function (data, mode, num) {
        if ( mode === void 0 ) mode='equal';
        if ( num === void 0 ) num=7;

        if (type(data) == 'array') {
            data = analyze(data);
        }
        var min = data.min;
        var max = data.max;
        var values = data.values.sort(function (a,b) { return a-b; });

        if (num === 1) { return [min,max]; }

        var limits = [];

        if (mode.substr(0,1) === 'c') { // continuous
            limits.push(min);
            limits.push(max);
        }

        if (mode.substr(0,1) === 'e') { // equal interval
            limits.push(min);
            for (var i=1; i<num; i++) {
                limits.push(min+((i/num)*(max-min)));
            }
            limits.push(max);
        }

        else if (mode.substr(0,1) === 'l') { // log scale
            if (min <= 0) {
                throw new Error('Logarithmic scales are only possible for values > 0');
            }
            var min_log = Math.LOG10E * log$1(min);
            var max_log = Math.LOG10E * log$1(max);
            limits.push(min);
            for (var i$1=1; i$1<num; i$1++) {
                limits.push(pow$7(10, min_log + ((i$1/num) * (max_log - min_log))));
            }
            limits.push(max);
        }

        else if (mode.substr(0,1) === 'q') { // quantile scale
            limits.push(min);
            for (var i$2=1; i$2<num; i$2++) {
                var p = ((values.length-1) * i$2)/num;
                var pb = floor$3(p);
                if (pb === p) {
                    limits.push(values[pb]);
                } else { // p > pb
                    var pr = p - pb;
                    limits.push((values[pb]*(1-pr)) + (values[pb+1]*pr));
                }
            }
            limits.push(max);

        }

        else if (mode.substr(0,1) === 'k') { // k-means clustering
            /*
            implementation based on
            http://code.google.com/p/figue/source/browse/trunk/figue.js#336
            simplified for 1-d input values
            */
            var cluster;
            var n = values.length;
            var assignments = new Array(n);
            var clusterSizes = new Array(num);
            var repeat = true;
            var nb_iters = 0;
            var centroids = null;

            // get seed values
            centroids = [];
            centroids.push(min);
            for (var i$3=1; i$3<num; i$3++) {
                centroids.push(min + ((i$3/num) * (max-min)));
            }
            centroids.push(max);

            while (repeat) {
                // assignment step
                for (var j=0; j<num; j++) {
                    clusterSizes[j] = 0;
                }
                for (var i$4=0; i$4<n; i$4++) {
                    var value = values[i$4];
                    var mindist = Number.MAX_VALUE;
                    var best = (void 0);
                    for (var j$1=0; j$1<num; j$1++) {
                        var dist = abs(centroids[j$1]-value);
                        if (dist < mindist) {
                            mindist = dist;
                            best = j$1;
                        }
                        clusterSizes[best]++;
                        assignments[i$4] = best;
                    }
                }

                // update centroids step
                var newCentroids = new Array(num);
                for (var j$2=0; j$2<num; j$2++) {
                    newCentroids[j$2] = null;
                }
                for (var i$5=0; i$5<n; i$5++) {
                    cluster = assignments[i$5];
                    if (newCentroids[cluster] === null) {
                        newCentroids[cluster] = values[i$5];
                    } else {
                        newCentroids[cluster] += values[i$5];
                    }
                }
                for (var j$3=0; j$3<num; j$3++) {
                    newCentroids[j$3] *= 1/clusterSizes[j$3];
                }

                // check convergence
                repeat = false;
                for (var j$4=0; j$4<num; j$4++) {
                    if (newCentroids[j$4] !== centroids[j$4]) {
                        repeat = true;
                        break;
                    }
                }

                centroids = newCentroids;
                nb_iters++;

                if (nb_iters > 200) {
                    repeat = false;
                }
            }

            // finished k-means clustering
            // the next part is borrowed from gabrielflor.it
            var kClusters = {};
            for (var j$5=0; j$5<num; j$5++) {
                kClusters[j$5] = [];
            }
            for (var i$6=0; i$6<n; i$6++) {
                cluster = assignments[i$6];
                kClusters[cluster].push(values[i$6]);
            }
            var tmpKMeansBreaks = [];
            for (var j$6=0; j$6<num; j$6++) {
                tmpKMeansBreaks.push(kClusters[j$6][0]);
                tmpKMeansBreaks.push(kClusters[j$6][kClusters[j$6].length-1]);
            }
            tmpKMeansBreaks = tmpKMeansBreaks.sort(function (a,b){ return a-b; });
            limits.push(tmpKMeansBreaks[0]);
            for (var i$7=1; i$7 < tmpKMeansBreaks.length; i$7+= 2) {
                var v = tmpKMeansBreaks[i$7];
                if (!isNaN(v) && (limits.indexOf(v) === -1)) {
                    limits.push(v);
                }
            }
        }
        return limits;
    };

    var analyze_1 = {analyze: analyze, limits: limits};

    var contrast = function (a, b) {
        // WCAG contrast ratio
        // see http://www.w3.org/TR/2008/REC-WCAG20-20081211/#contrast-ratiodef
        a = new Color_1(a);
        b = new Color_1(b);
        var l1 = a.luminance();
        var l2 = b.luminance();
        return l1 > l2 ? (l1 + 0.05) / (l2 + 0.05) : (l2 + 0.05) / (l1 + 0.05);
    };

    var sqrt$4 = Math.sqrt;
    var atan2$2 = Math.atan2;
    var abs$1 = Math.abs;
    var cos$4 = Math.cos;
    var PI$2 = Math.PI;

    var deltaE = function(a, b, L, C) {
        if ( L === void 0 ) L=1;
        if ( C === void 0 ) C=1;

        // Delta E (CMC)
        // see http://www.brucelindbloom.com/index.html?Eqn_DeltaE_CMC.html
        a = new Color_1(a);
        b = new Color_1(b);
        var ref = Array.from(a.lab());
        var L1 = ref[0];
        var a1 = ref[1];
        var b1 = ref[2];
        var ref$1 = Array.from(b.lab());
        var L2 = ref$1[0];
        var a2 = ref$1[1];
        var b2 = ref$1[2];
        var c1 = sqrt$4((a1 * a1) + (b1 * b1));
        var c2 = sqrt$4((a2 * a2) + (b2 * b2));
        var sl = L1 < 16.0 ? 0.511 : (0.040975 * L1) / (1.0 + (0.01765 * L1));
        var sc = ((0.0638 * c1) / (1.0 + (0.0131 * c1))) + 0.638;
        var h1 = c1 < 0.000001 ? 0.0 : (atan2$2(b1, a1) * 180.0) / PI$2;
        while (h1 < 0) { h1 += 360; }
        while (h1 >= 360) { h1 -= 360; }
        var t = (h1 >= 164.0) && (h1 <= 345.0) ? (0.56 + abs$1(0.2 * cos$4((PI$2 * (h1 + 168.0)) / 180.0))) : (0.36 + abs$1(0.4 * cos$4((PI$2 * (h1 + 35.0)) / 180.0)));
        var c4 = c1 * c1 * c1 * c1;
        var f = sqrt$4(c4 / (c4 + 1900.0));
        var sh = sc * (((f * t) + 1.0) - f);
        var delL = L1 - L2;
        var delC = c1 - c2;
        var delA = a1 - a2;
        var delB = b1 - b2;
        var dH2 = ((delA * delA) + (delB * delB)) - (delC * delC);
        var v1 = delL / (L * sl);
        var v2 = delC / (C * sc);
        var v3 = sh;
        return sqrt$4((v1 * v1) + (v2 * v2) + (dH2 / (v3 * v3)));
    };

    // simple Euclidean distance
    var distance = function(a, b, mode) {
        if ( mode === void 0 ) mode='lab';

        // Delta E (CIE 1976)
        // see http://www.brucelindbloom.com/index.html?Equations.html
        a = new Color_1(a);
        b = new Color_1(b);
        var l1 = a.get(mode);
        var l2 = b.get(mode);
        var sum_sq = 0;
        for (var i in l1) {
            var d = (l1[i] || 0) - (l2[i] || 0);
            sum_sq += d*d;
        }
        return Math.sqrt(sum_sq);
    };

    var valid = function () {
        var args = [], len = arguments.length;
        while ( len-- ) args[ len ] = arguments[ len ];

        try {
            new (Function.prototype.bind.apply( Color_1, [ null ].concat( args) ));
            return true;
        } catch (e) {
            return false;
        }
    };

    // some pre-defined color scales:




    var scales = {
    	cool: function cool() { return scale([chroma_1.hsl(180,1,.9), chroma_1.hsl(250,.7,.4)]) },
    	hot: function hot() { return scale(['#000','#f00','#ff0','#fff'], [0,.25,.75,1]).mode('rgb') }
    };

    /**
        ColorBrewer colors for chroma.js

        Copyright (c) 2002 Cynthia Brewer, Mark Harrower, and The
        Pennsylvania State University.

        Licensed under the Apache License, Version 2.0 (the "License");
        you may not use this file except in compliance with the License.
        You may obtain a copy of the License at
        http://www.apache.org/licenses/LICENSE-2.0

        Unless required by applicable law or agreed to in writing, software distributed
        under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
        CONDITIONS OF ANY KIND, either express or implied. See the License for the
        specific language governing permissions and limitations under the License.
    */

    var colorbrewer = {
        // sequential
        OrRd: ['#fff7ec', '#fee8c8', '#fdd49e', '#fdbb84', '#fc8d59', '#ef6548', '#d7301f', '#b30000', '#7f0000'],
        PuBu: ['#fff7fb', '#ece7f2', '#d0d1e6', '#a6bddb', '#74a9cf', '#3690c0', '#0570b0', '#045a8d', '#023858'],
        BuPu: ['#f7fcfd', '#e0ecf4', '#bfd3e6', '#9ebcda', '#8c96c6', '#8c6bb1', '#88419d', '#810f7c', '#4d004b'],
        Oranges: ['#fff5eb', '#fee6ce', '#fdd0a2', '#fdae6b', '#fd8d3c', '#f16913', '#d94801', '#a63603', '#7f2704'],
        BuGn: ['#f7fcfd', '#e5f5f9', '#ccece6', '#99d8c9', '#66c2a4', '#41ae76', '#238b45', '#006d2c', '#00441b'],
        YlOrBr: ['#ffffe5', '#fff7bc', '#fee391', '#fec44f', '#fe9929', '#ec7014', '#cc4c02', '#993404', '#662506'],
        YlGn: ['#ffffe5', '#f7fcb9', '#d9f0a3', '#addd8e', '#78c679', '#41ab5d', '#238443', '#006837', '#004529'],
        Reds: ['#fff5f0', '#fee0d2', '#fcbba1', '#fc9272', '#fb6a4a', '#ef3b2c', '#cb181d', '#a50f15', '#67000d'],
        RdPu: ['#fff7f3', '#fde0dd', '#fcc5c0', '#fa9fb5', '#f768a1', '#dd3497', '#ae017e', '#7a0177', '#49006a'],
        Greens: ['#f7fcf5', '#e5f5e0', '#c7e9c0', '#a1d99b', '#74c476', '#41ab5d', '#238b45', '#006d2c', '#00441b'],
        YlGnBu: ['#ffffd9', '#edf8b1', '#c7e9b4', '#7fcdbb', '#41b6c4', '#1d91c0', '#225ea8', '#253494', '#081d58'],
        Purples: ['#fcfbfd', '#efedf5', '#dadaeb', '#bcbddc', '#9e9ac8', '#807dba', '#6a51a3', '#54278f', '#3f007d'],
        GnBu: ['#f7fcf0', '#e0f3db', '#ccebc5', '#a8ddb5', '#7bccc4', '#4eb3d3', '#2b8cbe', '#0868ac', '#084081'],
        Greys: ['#ffffff', '#f0f0f0', '#d9d9d9', '#bdbdbd', '#969696', '#737373', '#525252', '#252525', '#000000'],
        YlOrRd: ['#ffffcc', '#ffeda0', '#fed976', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c', '#bd0026', '#800026'],
        PuRd: ['#f7f4f9', '#e7e1ef', '#d4b9da', '#c994c7', '#df65b0', '#e7298a', '#ce1256', '#980043', '#67001f'],
        Blues: ['#f7fbff', '#deebf7', '#c6dbef', '#9ecae1', '#6baed6', '#4292c6', '#2171b5', '#08519c', '#08306b'],
        PuBuGn: ['#fff7fb', '#ece2f0', '#d0d1e6', '#a6bddb', '#67a9cf', '#3690c0', '#02818a', '#016c59', '#014636'],
        Viridis: ['#440154', '#482777', '#3f4a8a', '#31678e', '#26838f', '#1f9d8a', '#6cce5a', '#b6de2b', '#fee825'],

        // diverging

        Spectral: ['#9e0142', '#d53e4f', '#f46d43', '#fdae61', '#fee08b', '#ffffbf', '#e6f598', '#abdda4', '#66c2a5', '#3288bd', '#5e4fa2'],
        RdYlGn: ['#a50026', '#d73027', '#f46d43', '#fdae61', '#fee08b', '#ffffbf', '#d9ef8b', '#a6d96a', '#66bd63', '#1a9850', '#006837'],
        RdBu: ['#67001f', '#b2182b', '#d6604d', '#f4a582', '#fddbc7', '#f7f7f7', '#d1e5f0', '#92c5de', '#4393c3', '#2166ac', '#053061'],
        PiYG: ['#8e0152', '#c51b7d', '#de77ae', '#f1b6da', '#fde0ef', '#f7f7f7', '#e6f5d0', '#b8e186', '#7fbc41', '#4d9221', '#276419'],
        PRGn: ['#40004b', '#762a83', '#9970ab', '#c2a5cf', '#e7d4e8', '#f7f7f7', '#d9f0d3', '#a6dba0', '#5aae61', '#1b7837', '#00441b'],
        RdYlBu: ['#a50026', '#d73027', '#f46d43', '#fdae61', '#fee090', '#ffffbf', '#e0f3f8', '#abd9e9', '#74add1', '#4575b4', '#313695'],
        BrBG: ['#543005', '#8c510a', '#bf812d', '#dfc27d', '#f6e8c3', '#f5f5f5', '#c7eae5', '#80cdc1', '#35978f', '#01665e', '#003c30'],
        RdGy: ['#67001f', '#b2182b', '#d6604d', '#f4a582', '#fddbc7', '#ffffff', '#e0e0e0', '#bababa', '#878787', '#4d4d4d', '#1a1a1a'],
        PuOr: ['#7f3b08', '#b35806', '#e08214', '#fdb863', '#fee0b6', '#f7f7f7', '#d8daeb', '#b2abd2', '#8073ac', '#542788', '#2d004b'],

        // qualitative

        Set2: ['#66c2a5', '#fc8d62', '#8da0cb', '#e78ac3', '#a6d854', '#ffd92f', '#e5c494', '#b3b3b3'],
        Accent: ['#7fc97f', '#beaed4', '#fdc086', '#ffff99', '#386cb0', '#f0027f', '#bf5b17', '#666666'],
        Set1: ['#e41a1c', '#377eb8', '#4daf4a', '#984ea3', '#ff7f00', '#ffff33', '#a65628', '#f781bf', '#999999'],
        Set3: ['#8dd3c7', '#ffffb3', '#bebada', '#fb8072', '#80b1d3', '#fdb462', '#b3de69', '#fccde5', '#d9d9d9', '#bc80bd', '#ccebc5', '#ffed6f'],
        Dark2: ['#1b9e77', '#d95f02', '#7570b3', '#e7298a', '#66a61e', '#e6ab02', '#a6761d', '#666666'],
        Paired: ['#a6cee3', '#1f78b4', '#b2df8a', '#33a02c', '#fb9a99', '#e31a1c', '#fdbf6f', '#ff7f00', '#cab2d6', '#6a3d9a', '#ffff99', '#b15928'],
        Pastel2: ['#b3e2cd', '#fdcdac', '#cbd5e8', '#f4cae4', '#e6f5c9', '#fff2ae', '#f1e2cc', '#cccccc'],
        Pastel1: ['#fbb4ae', '#b3cde3', '#ccebc5', '#decbe4', '#fed9a6', '#ffffcc', '#e5d8bd', '#fddaec', '#f2f2f2'],
    };

    // add lowercase aliases for case-insensitive matches
    for (var i$1 = 0, list$1 = Object.keys(colorbrewer); i$1 < list$1.length; i$1 += 1) {
        var key = list$1[i$1];

        colorbrewer[key.toLowerCase()] = colorbrewer[key];
    }

    var colorbrewer_1 = colorbrewer;

    // feel free to comment out anything to rollup
    // a smaller chroma.js built

    // io --> convert colors















    // operators --> modify existing Colors










    // interpolators










    // generators -- > create new colors
    chroma_1.average = average;
    chroma_1.bezier = bezier_1;
    chroma_1.blend = blend_1;
    chroma_1.cubehelix = cubehelix;
    chroma_1.mix = chroma_1.interpolate = mix;
    chroma_1.random = random_1;
    chroma_1.scale = scale;

    // other utility methods
    chroma_1.analyze = analyze_1.analyze;
    chroma_1.contrast = contrast;
    chroma_1.deltaE = deltaE;
    chroma_1.distance = distance;
    chroma_1.limits = analyze_1.limits;
    chroma_1.valid = valid;

    // scale
    chroma_1.scales = scales;

    // colors
    chroma_1.colors = w3cx11_1;
    chroma_1.brewer = colorbrewer_1;

    var chroma_js = chroma_1;

    return chroma_js;

})));


/***/ }),

/***/ 451:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(645);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".c-palette-builder>*+*{margin-top:15px}.c-palette-builder__source-list+.c-palette-builder__source-list{margin-top:10px}[class][class] .c-palette-builder__source-item{display:flex;align-items:center;margin-bottom:5px}[class][class] .c-palette-builder__source-item>*+*{margin-left:5px}", ""]);
// Exports
/* harmony default export */ __webpack_exports__["Z"] = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ 645:
/***/ (function(module) {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (cssWithMappingToString) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join('');
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === 'string') {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, '']];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

/***/ }),

/***/ 119:
/***/ (function(module) {

// Generated by Haxe 3.4.4
var hsluv = hsluv || {};
hsluv.Geometry = function() { };
hsluv.Geometry.intersectLineLine = function(a,b) {
	var x = (a.intercept - b.intercept) / (b.slope - a.slope);
	var y = a.slope * x + a.intercept;
	return { x : x, y : y};
};
hsluv.Geometry.distanceFromOrigin = function(point) {
	return Math.sqrt(Math.pow(point.x,2) + Math.pow(point.y,2));
};
hsluv.Geometry.distanceLineFromOrigin = function(line) {
	return Math.abs(line.intercept) / Math.sqrt(Math.pow(line.slope,2) + 1);
};
hsluv.Geometry.perpendicularThroughPoint = function(line,point) {
	var slope = -1 / line.slope;
	var intercept = point.y - slope * point.x;
	return { slope : slope, intercept : intercept};
};
hsluv.Geometry.angleFromOrigin = function(point) {
	return Math.atan2(point.y,point.x);
};
hsluv.Geometry.normalizeAngle = function(angle) {
	var m = 2 * Math.PI;
	return (angle % m + m) % m;
};
hsluv.Geometry.lengthOfRayUntilIntersect = function(theta,line) {
	return line.intercept / (Math.sin(theta) - line.slope * Math.cos(theta));
};
hsluv.Hsluv = function() { };
hsluv.Hsluv.getBounds = function(L) {
	var result = [];
	var sub1 = Math.pow(L + 16,3) / 1560896;
	var sub2 = sub1 > hsluv.Hsluv.epsilon ? sub1 : L / hsluv.Hsluv.kappa;
	var _g = 0;
	while(_g < 3) {
		var c = _g++;
		var m1 = hsluv.Hsluv.m[c][0];
		var m2 = hsluv.Hsluv.m[c][1];
		var m3 = hsluv.Hsluv.m[c][2];
		var _g1 = 0;
		while(_g1 < 2) {
			var t = _g1++;
			var top1 = (284517 * m1 - 94839 * m3) * sub2;
			var top2 = (838422 * m3 + 769860 * m2 + 731718 * m1) * L * sub2 - 769860 * t * L;
			var bottom = (632260 * m3 - 126452 * m2) * sub2 + 126452 * t;
			result.push({ slope : top1 / bottom, intercept : top2 / bottom});
		}
	}
	return result;
};
hsluv.Hsluv.maxSafeChromaForL = function(L) {
	var bounds = hsluv.Hsluv.getBounds(L);
	var min = Infinity;
	var _g = 0;
	while(_g < bounds.length) {
		var bound = bounds[_g];
		++_g;
		var length = hsluv.Geometry.distanceLineFromOrigin(bound);
		min = Math.min(min,length);
	}
	return min;
};
hsluv.Hsluv.maxChromaForLH = function(L,H) {
	var hrad = H / 360 * Math.PI * 2;
	var bounds = hsluv.Hsluv.getBounds(L);
	var min = Infinity;
	var _g = 0;
	while(_g < bounds.length) {
		var bound = bounds[_g];
		++_g;
		var length = hsluv.Geometry.lengthOfRayUntilIntersect(hrad,bound);
		if(length >= 0) {
			min = Math.min(min,length);
		}
	}
	return min;
};
hsluv.Hsluv.dotProduct = function(a,b) {
	var sum = 0;
	var _g1 = 0;
	var _g = a.length;
	while(_g1 < _g) {
		var i = _g1++;
		sum += a[i] * b[i];
	}
	return sum;
};
hsluv.Hsluv.fromLinear = function(c) {
	if(c <= 0.0031308) {
		return 12.92 * c;
	} else {
		return 1.055 * Math.pow(c,0.416666666666666685) - 0.055;
	}
};
hsluv.Hsluv.toLinear = function(c) {
	if(c > 0.04045) {
		return Math.pow((c + 0.055) / 1.055,2.4);
	} else {
		return c / 12.92;
	}
};
hsluv.Hsluv.xyzToRgb = function(tuple) {
	return [hsluv.Hsluv.fromLinear(hsluv.Hsluv.dotProduct(hsluv.Hsluv.m[0],tuple)),hsluv.Hsluv.fromLinear(hsluv.Hsluv.dotProduct(hsluv.Hsluv.m[1],tuple)),hsluv.Hsluv.fromLinear(hsluv.Hsluv.dotProduct(hsluv.Hsluv.m[2],tuple))];
};
hsluv.Hsluv.rgbToXyz = function(tuple) {
	var rgbl = [hsluv.Hsluv.toLinear(tuple[0]),hsluv.Hsluv.toLinear(tuple[1]),hsluv.Hsluv.toLinear(tuple[2])];
	return [hsluv.Hsluv.dotProduct(hsluv.Hsluv.minv[0],rgbl),hsluv.Hsluv.dotProduct(hsluv.Hsluv.minv[1],rgbl),hsluv.Hsluv.dotProduct(hsluv.Hsluv.minv[2],rgbl)];
};
hsluv.Hsluv.yToL = function(Y) {
	if(Y <= hsluv.Hsluv.epsilon) {
		return Y / hsluv.Hsluv.refY * hsluv.Hsluv.kappa;
	} else {
		return 116 * Math.pow(Y / hsluv.Hsluv.refY,0.333333333333333315) - 16;
	}
};
hsluv.Hsluv.lToY = function(L) {
	if(L <= 8) {
		return hsluv.Hsluv.refY * L / hsluv.Hsluv.kappa;
	} else {
		return hsluv.Hsluv.refY * Math.pow((L + 16) / 116,3);
	}
};
hsluv.Hsluv.xyzToLuv = function(tuple) {
	var X = tuple[0];
	var Y = tuple[1];
	var Z = tuple[2];
	var divider = X + 15 * Y + 3 * Z;
	var varU = 4 * X;
	var varV = 9 * Y;
	if(divider != 0) {
		varU /= divider;
		varV /= divider;
	} else {
		varU = NaN;
		varV = NaN;
	}
	var L = hsluv.Hsluv.yToL(Y);
	if(L == 0) {
		return [0,0,0];
	}
	var U = 13 * L * (varU - hsluv.Hsluv.refU);
	var V = 13 * L * (varV - hsluv.Hsluv.refV);
	return [L,U,V];
};
hsluv.Hsluv.luvToXyz = function(tuple) {
	var L = tuple[0];
	var U = tuple[1];
	var V = tuple[2];
	if(L == 0) {
		return [0,0,0];
	}
	var varU = U / (13 * L) + hsluv.Hsluv.refU;
	var varV = V / (13 * L) + hsluv.Hsluv.refV;
	var Y = hsluv.Hsluv.lToY(L);
	var X = 0 - 9 * Y * varU / ((varU - 4) * varV - varU * varV);
	var Z = (9 * Y - 15 * varV * Y - varV * X) / (3 * varV);
	return [X,Y,Z];
};
hsluv.Hsluv.luvToLch = function(tuple) {
	var L = tuple[0];
	var U = tuple[1];
	var V = tuple[2];
	var C = Math.sqrt(U * U + V * V);
	var H;
	if(C < 0.00000001) {
		H = 0;
	} else {
		var Hrad = Math.atan2(V,U);
		H = Hrad * 180.0 / Math.PI;
		if(H < 0) {
			H = 360 + H;
		}
	}
	return [L,C,H];
};
hsluv.Hsluv.lchToLuv = function(tuple) {
	var L = tuple[0];
	var C = tuple[1];
	var H = tuple[2];
	var Hrad = H / 360.0 * 2 * Math.PI;
	var U = Math.cos(Hrad) * C;
	var V = Math.sin(Hrad) * C;
	return [L,U,V];
};
hsluv.Hsluv.hsluvToLch = function(tuple) {
	var H = tuple[0];
	var S = tuple[1];
	var L = tuple[2];
	if(L > 99.9999999) {
		return [100,0,H];
	}
	if(L < 0.00000001) {
		return [0,0,H];
	}
	var max = hsluv.Hsluv.maxChromaForLH(L,H);
	var C = max / 100 * S;
	return [L,C,H];
};
hsluv.Hsluv.lchToHsluv = function(tuple) {
	var L = tuple[0];
	var C = tuple[1];
	var H = tuple[2];
	if(L > 99.9999999) {
		return [H,0,100];
	}
	if(L < 0.00000001) {
		return [H,0,0];
	}
	var max = hsluv.Hsluv.maxChromaForLH(L,H);
	var S = C / max * 100;
	return [H,S,L];
};
hsluv.Hsluv.hpluvToLch = function(tuple) {
	var H = tuple[0];
	var S = tuple[1];
	var L = tuple[2];
	if(L > 99.9999999) {
		return [100,0,H];
	}
	if(L < 0.00000001) {
		return [0,0,H];
	}
	var max = hsluv.Hsluv.maxSafeChromaForL(L);
	var C = max / 100 * S;
	return [L,C,H];
};
hsluv.Hsluv.lchToHpluv = function(tuple) {
	var L = tuple[0];
	var C = tuple[1];
	var H = tuple[2];
	if(L > 99.9999999) {
		return [H,0,100];
	}
	if(L < 0.00000001) {
		return [H,0,0];
	}
	var max = hsluv.Hsluv.maxSafeChromaForL(L);
	var S = C / max * 100;
	return [H,S,L];
};
hsluv.Hsluv.rgbToHex = function(tuple) {
	var h = "#";
	var _g = 0;
	while(_g < 3) {
		var i = _g++;
		var chan = tuple[i];
		var c = Math.round(chan * 255);
		var digit2 = c % 16;
		var digit1 = (c - digit2) / 16 | 0;
		h += hsluv.Hsluv.hexChars.charAt(digit1) + hsluv.Hsluv.hexChars.charAt(digit2);
	}
	return h;
};
hsluv.Hsluv.hexToRgb = function(hex) {
	hex = hex.toLowerCase();
	var ret = [];
	var _g = 0;
	while(_g < 3) {
		var i = _g++;
		var digit1 = hsluv.Hsluv.hexChars.indexOf(hex.charAt(i * 2 + 1));
		var digit2 = hsluv.Hsluv.hexChars.indexOf(hex.charAt(i * 2 + 2));
		var n = digit1 * 16 + digit2;
		ret.push(n / 255.0);
	}
	return ret;
};
hsluv.Hsluv.lchToRgb = function(tuple) {
	return hsluv.Hsluv.xyzToRgb(hsluv.Hsluv.luvToXyz(hsluv.Hsluv.lchToLuv(tuple)));
};
hsluv.Hsluv.rgbToLch = function(tuple) {
	return hsluv.Hsluv.luvToLch(hsluv.Hsluv.xyzToLuv(hsluv.Hsluv.rgbToXyz(tuple)));
};
hsluv.Hsluv.hsluvToRgb = function(tuple) {
	return hsluv.Hsluv.lchToRgb(hsluv.Hsluv.hsluvToLch(tuple));
};
hsluv.Hsluv.rgbToHsluv = function(tuple) {
	return hsluv.Hsluv.lchToHsluv(hsluv.Hsluv.rgbToLch(tuple));
};
hsluv.Hsluv.hpluvToRgb = function(tuple) {
	return hsluv.Hsluv.lchToRgb(hsluv.Hsluv.hpluvToLch(tuple));
};
hsluv.Hsluv.rgbToHpluv = function(tuple) {
	return hsluv.Hsluv.lchToHpluv(hsluv.Hsluv.rgbToLch(tuple));
};
hsluv.Hsluv.hsluvToHex = function(tuple) {
	return hsluv.Hsluv.rgbToHex(hsluv.Hsluv.hsluvToRgb(tuple));
};
hsluv.Hsluv.hpluvToHex = function(tuple) {
	return hsluv.Hsluv.rgbToHex(hsluv.Hsluv.hpluvToRgb(tuple));
};
hsluv.Hsluv.hexToHsluv = function(s) {
	return hsluv.Hsluv.rgbToHsluv(hsluv.Hsluv.hexToRgb(s));
};
hsluv.Hsluv.hexToHpluv = function(s) {
	return hsluv.Hsluv.rgbToHpluv(hsluv.Hsluv.hexToRgb(s));
};
hsluv.Hsluv.m = [[3.240969941904521,-1.537383177570093,-0.498610760293],[-0.96924363628087,1.87596750150772,0.041555057407175],[0.055630079696993,-0.20397695888897,1.056971514242878]];
hsluv.Hsluv.minv = [[0.41239079926595,0.35758433938387,0.18048078840183],[0.21263900587151,0.71516867876775,0.072192315360733],[0.019330818715591,0.11919477979462,0.95053215224966]];
hsluv.Hsluv.refY = 1.0;
hsluv.Hsluv.refU = 0.19783000664283;
hsluv.Hsluv.refV = 0.46831999493879;
hsluv.Hsluv.kappa = 903.2962962;
hsluv.Hsluv.epsilon = 0.0088564516;
hsluv.Hsluv.hexChars = "0123456789abcdef";
var root = {
    "hsluvToRgb": hsluv.Hsluv.hsluvToRgb,
    "rgbToHsluv": hsluv.Hsluv.rgbToHsluv,
    "hpluvToRgb": hsluv.Hsluv.hpluvToRgb,
    "rgbToHpluv": hsluv.Hsluv.rgbToHpluv,
    "hsluvToHex": hsluv.Hsluv.hsluvToHex,
    "hexToHsluv": hsluv.Hsluv.hexToHsluv,
    "hpluvToHex": hsluv.Hsluv.hpluvToHex,
    "hexToHpluv": hsluv.Hsluv.hexToHpluv,
    "lchToHpluv": hsluv.Hsluv.lchToHpluv,
    "hpluvToLch": hsluv.Hsluv.hpluvToLch,
    "lchToHsluv": hsluv.Hsluv.lchToHsluv,
    "hsluvToLch": hsluv.Hsluv.hsluvToLch,
    "lchToLuv": hsluv.Hsluv.lchToLuv,
    "luvToLch": hsluv.Hsluv.luvToLch,
    "xyzToLuv": hsluv.Hsluv.xyzToLuv,
    "luvToXyz": hsluv.Hsluv.luvToXyz,
    "xyzToRgb": hsluv.Hsluv.xyzToRgb,
    "rgbToXyz": hsluv.Hsluv.rgbToXyz,
    "lchToRgb": hsluv.Hsluv.lchToRgb,
    "rgbToLch": hsluv.Hsluv.rgbToLch
};

module.exports = root;


/***/ }),

/***/ 379:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


var isOldIE = function isOldIE() {
  var memo;
  return function memorize() {
    if (typeof memo === 'undefined') {
      // Test for IE <= 9 as proposed by Browserhacks
      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
      // Tests for existence of standard globals is to allow style-loader
      // to operate correctly into non-standard environments
      // @see https://github.com/webpack-contrib/style-loader/issues/177
      memo = Boolean(window && document && document.all && !window.atob);
    }

    return memo;
  };
}();

var getTarget = function getTarget() {
  var memo = {};
  return function memorize(target) {
    if (typeof memo[target] === 'undefined') {
      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself

      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
        try {
          // This will throw an exception if access to iframe is blocked
          // due to cross-origin restrictions
          styleTarget = styleTarget.contentDocument.head;
        } catch (e) {
          // istanbul ignore next
          styleTarget = null;
        }
      }

      memo[target] = styleTarget;
    }

    return memo[target];
  };
}();

var stylesInDom = [];

function getIndexByIdentifier(identifier) {
  var result = -1;

  for (var i = 0; i < stylesInDom.length; i++) {
    if (stylesInDom[i].identifier === identifier) {
      result = i;
      break;
    }
  }

  return result;
}

function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];

  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var index = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3]
    };

    if (index !== -1) {
      stylesInDom[index].references++;
      stylesInDom[index].updater(obj);
    } else {
      stylesInDom.push({
        identifier: identifier,
        updater: addStyle(obj, options),
        references: 1
      });
    }

    identifiers.push(identifier);
  }

  return identifiers;
}

function insertStyleElement(options) {
  var style = document.createElement('style');
  var attributes = options.attributes || {};

  if (typeof attributes.nonce === 'undefined') {
    var nonce =  true ? __webpack_require__.nc : 0;

    if (nonce) {
      attributes.nonce = nonce;
    }
  }

  Object.keys(attributes).forEach(function (key) {
    style.setAttribute(key, attributes[key]);
  });

  if (typeof options.insert === 'function') {
    options.insert(style);
  } else {
    var target = getTarget(options.insert || 'head');

    if (!target) {
      throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
    }

    target.appendChild(style);
  }

  return style;
}

function removeStyleElement(style) {
  // istanbul ignore if
  if (style.parentNode === null) {
    return false;
  }

  style.parentNode.removeChild(style);
}
/* istanbul ignore next  */


var replaceText = function replaceText() {
  var textStore = [];
  return function replace(index, replacement) {
    textStore[index] = replacement;
    return textStore.filter(Boolean).join('\n');
  };
}();

function applyToSingletonTag(style, index, remove, obj) {
  var css = remove ? '' : obj.media ? "@media ".concat(obj.media, " {").concat(obj.css, "}") : obj.css; // For old IE

  /* istanbul ignore if  */

  if (style.styleSheet) {
    style.styleSheet.cssText = replaceText(index, css);
  } else {
    var cssNode = document.createTextNode(css);
    var childNodes = style.childNodes;

    if (childNodes[index]) {
      style.removeChild(childNodes[index]);
    }

    if (childNodes.length) {
      style.insertBefore(cssNode, childNodes[index]);
    } else {
      style.appendChild(cssNode);
    }
  }
}

function applyToTag(style, options, obj) {
  var css = obj.css;
  var media = obj.media;
  var sourceMap = obj.sourceMap;

  if (media) {
    style.setAttribute('media', media);
  } else {
    style.removeAttribute('media');
  }

  if (sourceMap && typeof btoa !== 'undefined') {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  } // For old IE

  /* istanbul ignore if  */


  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    while (style.firstChild) {
      style.removeChild(style.firstChild);
    }

    style.appendChild(document.createTextNode(css));
  }
}

var singleton = null;
var singletonCounter = 0;

function addStyle(obj, options) {
  var style;
  var update;
  var remove;

  if (options.singleton) {
    var styleIndex = singletonCounter++;
    style = singleton || (singleton = insertStyleElement(options));
    update = applyToSingletonTag.bind(null, style, styleIndex, false);
    remove = applyToSingletonTag.bind(null, style, styleIndex, true);
  } else {
    style = insertStyleElement(options);
    update = applyToTag.bind(null, style, options);

    remove = function remove() {
      removeStyleElement(style);
    };
  }

  update(obj);
  return function updateStyle(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {
        return;
      }

      update(obj = newObj);
    } else {
      remove();
    }
  };
}

module.exports = function (list, options) {
  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
  // tags it will allow on a page

  if (!options.singleton && typeof options.singleton !== 'boolean') {
    options.singleton = isOldIE();
  }

  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];

    if (Object.prototype.toString.call(newList) !== '[object Array]') {
      return;
    }

    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDom[index].references--;
    }

    var newLastIdentifiers = modulesToDom(newList, options);

    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];

      var _index = getIndexByIdentifier(_identifier);

      if (stylesInDom[_index].references === 0) {
        stylesInDom[_index].updater();

        stylesInDom.splice(_index, 1);
      }
    }

    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ }),

/***/ 609:
/***/ (function(module) {

module.exports = (function() { return this["jQuery"]; }());

/***/ }),

/***/ 804:
/***/ (function(module) {

module.exports = (function() { return this["lodash"]; }());

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	// module exports must be returned from runtime so entry inlining is disabled
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(815);
/******/ })()
;