/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 946:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(645);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".sm-overlay{position:fixed;top:0;left:0;z-index:9999;width:100%;height:100%;background:#fff;transition:all .2s ease;transition-property:opacity}.sm-overlay:not(.sm-overlay--visible){opacity:0;pointer-events:none}.sm-overlay,.sm-overlay *{font-family:system-ui}.palette-preview{display:grid;grid-template-columns:5em 1fr [content-start] minmax(auto, 800px) [content-end] 1fr 5em;margin:5em 0}.palette-preview>*{grid-column:content-start/content-end}.palette-preview>*+*{margin-top:1em}.palette-preview-header{display:flex;align-items:center;margin-left:-1em}.palette-preview-header>*{margin-left:1em}.palette-preview-set{--swatch-gap: 0.25em;position:relative;display:flex;margin-left:calc( -1 * var(--swatch-gap) );width:100%}.palette-preview-set>*{margin-left:var(--swatch-gap)}.palette-preview-swatches{display:flex;flex-direction:column;flex-grow:1}.palette-preview-swatches>:not(:first-child):before{padding-top:50%}.palette-preview-swatches>*{flex:1 1 0}.palette-preview-swatches>*:before{padding-top:100%;display:block;content:\"\";background:currentColor}.palette-preview-accent{--swatch-width: calc( 100% / 12 - var(--swatch-gap) );display:flex;position:absolute;top:0;left:0;width:calc( 100% * 14 / 24 );height:100%;padding:calc( 0.5 * var(--swatch-width) );padding-bottom:calc( 0.25 * var(--swatch-width) );clip-path:polygon(0 62.5%, 50% 62.5%, 50% 0, 100% 0, 100% 100%, 0 100%)}.palette-preview-accent:before{content:\"\";display:block;width:100%;border-bottom-left-radius:1em;border-bottom-right-radius:1em;border:2px dashed currentColor;border-top:0}.palette-preview-source{display:flex;flex-direction:row-reverse;justify-content:flex-start;margin-left:-5em;width:5em}.palette-preview-source>:not(:first-child){margin-right:-1em}.palette-preview-source-color{width:2em;flex:0 1 auto;background:currentColor;border-radius:50%}.palette-preview-source-color:before{content:\"\";display:block;padding-top:100%}", ""]);
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
    }).join("");
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === "string") {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, ""]];
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
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
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
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js
var injectStylesIntoStyleTag = __webpack_require__(379);
var injectStylesIntoStyleTag_default = /*#__PURE__*/__webpack_require__.n(injectStylesIntoStyleTag);
// EXTERNAL MODULE: ./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/_js/customizer-preview/style.scss
var style = __webpack_require__(946);
;// CONCATENATED MODULE: ./src/_js/customizer-preview/style.scss

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = injectStylesIntoStyleTag_default()(style/* default */.Z, options);



/* harmony default export */ var customizer_preview_style = (style/* default.locals */.Z.locals || {});
// EXTERNAL MODULE: external "jQuery"
var external_jQuery_ = __webpack_require__(609);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);
// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(804);
var external_lodash_default = /*#__PURE__*/__webpack_require__.n(external_lodash_);
;// CONCATENATED MODULE: ./src/_js/customizer-preview/utils.js
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }


 // Mirror logic of server-side Utils\Fonts::getCSSValue()

var getFontFieldCSSValue = function getFontFieldCSSValue(settingID, value) {
  var CSSValue = {};

  if (typeof value.font_family !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.font_family)) {
    CSSValue['font-family'] = value.font_family; // "Expand" the font family by appending the fallback stack, if any is available.
    // But only do this, if the value is not already a font stack!

    if (CSSValue['font-family'].indexOf(',') === -1) {
      var fallbackStack = getFontFamilyFallbackStack(CSSValue['font-family']);

      if (fallbackStack.length) {
        CSSValue['font-family'] += ',' + fallbackStack;
      }
    }

    CSSValue['font-family'] = sanitizeFontFamilyCSSValue(CSSValue['font-family']);
  }

  if (typeof value.font_variant !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.font_variant)) {
    var variant = value.font_variant;

    if (external_lodash_default().isString(variant)) {
      // We may have a style in the variant; attempt to split.
      if (variant.indexOf('italic') !== -1) {
        CSSValue['font-style'] = 'italic';
        variant = variant.replace('italic', '');
      } else if (variant.indexOf('oblique') !== -1) {
        CSSValue['font-style'] = 'oblique';
        variant = variant.replace('oblique', '');
      } // If anything remained, then we have a font weight also.


      if (variant !== '') {
        if (variant === 'regular' || variant === 'normal') {
          variant = '400';
        }

        CSSValue['font-weight'] = variant;
      }
    } else if (external_lodash_default().isNumber(variant)) {
      CSSValue['font-weight'] = String(variant);
    }
  }

  if (typeof value.font_size !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.font_size)) {
    var fontSizeUnit = false;
    CSSValue['font-size'] = value.font_size; // If the value already contains a unit (is not numeric), go with that.

    if (isNaN(value.font_size)) {
      // If we have a standardized value field (as array), use that.
      if (typeof value.font_size.value !== 'undefined') {
        CSSValue['font-size'] = value.font_size.value;

        if (typeof value.font_size.unit !== 'undefined') {
          fontSizeUnit = value.font_size.unit;
        }
      } else {
        fontSizeUnit = getFieldUnit(settingID, 'font-size');
      }
    } else {
      fontSizeUnit = getFieldUnit(settingID, 'font-size');
    }

    if (false !== fontSizeUnit) {
      CSSValue['font-size'] += fontSizeUnit;
    }
  }

  if (typeof value.letter_spacing !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.letter_spacing)) {
    var letterSpacingUnit = false;
    CSSValue['letter-spacing'] = value.letter_spacing; // If the value already contains a unit (is not numeric), go with that.

    if (isNaN(value.letter_spacing)) {
      // If we have a standardized value field (as array), use that.
      if (typeof value.letter_spacing.value !== 'undefined') {
        CSSValue['letter-spacing'] = value.letter_spacing.value;

        if (typeof value.letter_spacing.unit !== 'undefined') {
          letterSpacingUnit = value.letter_spacing.unit;
        }
      } else {
        letterSpacingUnit = getFieldUnit(settingID, 'letter-spacing');
      }
    } else {
      letterSpacingUnit = getFieldUnit(settingID, 'letter-spacing');
    }

    if (false !== letterSpacingUnit) {
      CSSValue['letter-spacing'] += letterSpacingUnit;
    }
  }

  if (typeof value.line_height !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.line_height)) {
    var lineHeightUnit = false;
    CSSValue['line-height'] = value.line_height; // If the value already contains a unit (is not numeric), go with that.

    if (isNaN(value.line_height)) {
      // If we have a standardized value field (as array), use that.
      if (typeof value.line_height.value !== 'undefined') {
        CSSValue['line-height'] = value.line_height.value;

        if (typeof value.line_height.unit !== 'undefined') {
          lineHeightUnit = value.line_height.unit;
        }
      } else {
        lineHeightUnit = getFieldUnit(settingID, 'line-height');
      }
    } else {
      lineHeightUnit = getFieldUnit(settingID, 'line-height');
    }

    if (false !== lineHeightUnit) {
      CSSValue['line-height'] += lineHeightUnit;
    }
  }

  if (typeof value.text_align !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.text_align)) {
    CSSValue['text-align'] = value.text_align;
  }

  if (typeof value.text_transform !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.text_transform)) {
    CSSValue['text-transform'] = value.text_transform;
  }

  if (typeof value.text_decoration !== 'undefined' && !external_lodash_default().includes(['', 'false', false], value.text_decoration)) {
    CSSValue['text-decoration'] = value.text_decoration;
  }

  return CSSValue;
}; // Mirror logic of server-side Utils\Fonts::getFontStyle()

var getFontFieldCSSCode = function getFontFieldCSSCode(settingID, cssValue, value) {
  var fontConfig = customify.config.settings[settingID];
  var prefix = typeof fontConfig.properties_prefix === 'undefined' ? '' : fontConfig.properties_prefix;
  var output = '';

  if (typeof window !== 'undefined' && typeof fontConfig.callback !== 'undefined' && typeof window[fontConfig.callback] === 'function') {
    // The callbacks expect a string selector right now, not a standardized list.
    // @todo Maybe migrate all callbacks to the new standardized data and remove all this.
    var plainSelectors = [];

    external_lodash_default().each(fontConfig.selector, function (details, selector) {
      plainSelectors.push(selector);
    });

    var adjustedFontConfig = external_jQuery_default().extend(true, {}, fontConfig);
    adjustedFontConfig.selector = plainSelectors.join(', '); // Also, "kill" all fields unit since we pass final CSS values.
    // @todo For some reason, the client-side Typeline cbs are not consistent and expect the font-size value with unit.

    external_lodash_default().each(adjustedFontConfig['fields'], function (fieldValue, fieldKey) {
      if (typeof fieldValue.unit !== 'undefined') {
        adjustedFontConfig['fields'][fieldKey]['unit'] = false;
      }
    }); // Callbacks want the value keys with underscores, not dashes.
    // We will provide them in both versions for a smoother transition.


    external_lodash_default().each(cssValue, function (propertyValue, property) {
      var newKey = property.replace(regexForMultipleReplace, '_');
      cssValue[newKey] = propertyValue;
    });

    return window[fontConfig.callback](cssValue, adjustedFontConfig);
  }

  if (typeof fontConfig.selector === 'undefined' || external_lodash_default().isEmpty(fontConfig.selector) || external_lodash_default().isEmpty(cssValue)) {
    return output;
  } // The general CSS allowed properties.


  var subFieldsCSSAllowedProperties = extractAllowedCSSPropertiesFromFontFields(fontConfig['fields']); // The selector is standardized to a list of simple string selectors, or a list of complex selectors with details.
  // In either case, the actual selector is in the key, and the value is an array (possibly empty).
  // Since we might have simple CSS selectors and complex ones (with special details),
  // for cleanliness we will group the simple ones under a single CSS rule,
  // and output individual CSS rules for complex ones.
  // Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.

  var simpleCSSSelectors = [];
  var complexCSSSelectors = {};

  external_lodash_default().each(fontConfig.selector, function (details, selector) {
    if (external_lodash_default().isEmpty(details.properties)) {
      // This is a simple selector.
      simpleCSSSelectors.push(selector);
    } else {
      complexCSSSelectors[selector] = details;
    }
  });

  if (!external_lodash_default().isEmpty(simpleCSSSelectors)) {
    output += '\n' + simpleCSSSelectors.join(', ') + ' {\n';
    output += getFontFieldCSSProperties(cssValue, subFieldsCSSAllowedProperties, prefix);
    output += '}\n';
  }

  if (!external_lodash_default().isEmpty(complexCSSSelectors)) {
    external_lodash_default().each(complexCSSSelectors, function (details, selector) {
      output += '\n' + selector + ' {\n';
      output += getFontFieldCSSProperties(cssValue, details.properties, prefix);
      output += '}\n';
    });
  }

  return output;
}; // This is a mirror logic of the server-side Utils\Fonts::getSubFieldUnit()

var getFieldUnit = function getFieldUnit(settingID, field) {
  if (typeof customify.config.settings[settingID] === 'undefined' || typeof customify.config.settings[settingID].fields[field] === 'undefined') {
    // These fields don't have an unit, by default.
    if (external_lodash_default().includes(['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'], field)) {
      return false;
    } // The rest of the subfields have pixels as default units.


    return 'px';
  }

  if (typeof customify.config.settings[settingID].fields[field].unit !== 'undefined') {
    // Make sure that we convert all falsy unit values to the boolean false.
    return external_lodash_default().includes(['', 'false', false], customify.config.settings[settingID].fields[field].unit) ? false : customify.config.settings[settingID].fields[field].unit;
  }

  if (typeof customify.config.settings[settingID].fields[field][3] !== 'undefined') {
    // Make sure that we convert all falsy unit values to the boolean false.
    return external_lodash_default().includes(['', 'false', false], customify.config.settings[settingID].fields[field][3]) ? false : customify.config.settings[settingID].fields[field][3];
  }

  return 'px';
}; // Mirror logic of server-side Utils\Fonts::getCSSProperties()

var getFontFieldCSSProperties = function getFontFieldCSSProperties(cssValue) {
  var allowedProperties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  var prefix = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
  var output = '';
  external_jQuery_default().each(cssValue, function (property, propertyValue) {
    // We don't want to output empty CSS rules.
    if ('' === propertyValue || false === propertyValue) {
      return;
    } // If the property is not allowed, skip it.


    if (!isCSSPropertyAllowed(property, allowedProperties)) {
      return;
    }

    output += prefix + property + ': ' + propertyValue + ';\n';
  });
  return output;
}; // Mirror logic of server-side Utils\Fonts::isCSSPropertyAllowed()


var isCSSPropertyAllowed = function isCSSPropertyAllowed(property) {
  var allowedProperties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  // Empty properties are not allowed.
  if (external_lodash_default().isEmpty(property)) {
    return false;
  } // Everything is allowed if nothing is specified.


  if (external_lodash_default().isEmpty(allowedProperties)) {
    return true;
  } // For arrays


  if (external_lodash_default().includes(allowedProperties, property)) {
    return true;
  } // For objects


  if (external_lodash_default().has(allowedProperties, property) && allowedProperties[property]) {
    return true;
  }

  return false;
};

var extractAllowedCSSPropertiesFromFontFields = function extractAllowedCSSPropertiesFromFontFields(subfields) {
  // Nothing is allowed by default.
  var allowedProperties = {
    'font-family': false,
    'font-weight': false,
    'font-style': false,
    'font-size': false,
    'line-height': false,
    'letter-spacing': false,
    'text-align': false,
    'text-transform': false,
    'text-decoration': false
  };

  if (external_lodash_default().isEmpty(subfields)) {
    return allowedProperties;
  } // We will match the subfield keys with the CSS properties, but only those that properties that are allowed.
  // Maybe at some point some more complex matching would be needed here.


  external_lodash_default().each(subfields, function (value, key) {
    if (typeof allowedProperties[key] !== 'undefined') {
      // Convert values to boolean.
      allowedProperties[key] = !!value; // For font-weight we want font-style to go the same way,
      // since these two are generated from the same subfield: font-weight (actually holding the font variant value).

      if ('font-weight' === key) {
        allowedProperties['font-style'] = allowedProperties[key];
      }
    }
  });

  return allowedProperties;
};

var maybeLoadFontFamily = function maybeLoadFontFamily(font, settingID) {
  if (typeof font.font_family === 'undefined') {
    return;
  }

  var fontConfig = customify.config.settings[settingID];
  var family = font.font_family; // The font family may be a comma separated list like "Roboto, sans"

  var fontType = parent.sm.customizer.determineFontType(family);

  if ('system_font' === fontType) {
    // Nothing to do for standard fonts
    return;
  }

  var fontDetails = parent.sm.customizer.getFontDetails(family, fontType); // Handle theme defined fonts and cloud fonts together since they are very similar.

  if (fontType === 'theme_font' || fontType === 'cloud_font') {
    // Bail if we have no src.
    if (_typeof(fontDetails.src) === undefined) {
      return;
    } // Handle the font variants.
    // If there is a selected font variant and we haven't been instructed to load all, load only that,
    // otherwise load all the available variants.


    var variants = typeof font.font_variant !== 'undefined' && (typeof fontConfig['fields']['font-weight']['loadAllVariants'] === 'undefined' || !fontConfig['fields']['font-weight']['loadAllVariants']) && typeof fontDetails.variants !== 'undefined' // If the font has no variants, any variant value we may have received should be ignored.
    && external_lodash_default().includes(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
    ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : [];

    if (!external_lodash_default().isEmpty(variants)) {
      variants = standardizeToArray(variants);

      if (!external_lodash_default().isEmpty(variants)) {
        family = family + ':' + variants.map(function (variant) {
          return parent.sm.customizer.convertFontVariantToFVD(variant);
        }).join(',');
      }
    }

    if (fontsCache.indexOf(family) === -1) {
      WebFont.load({
        custom: {
          families: [family],
          urls: [fontDetails.src]
        },
        classes: false,
        events: false
      }); // Remember we've loaded this family (with it's variants) so we don't load it again.

      fontsCache.push(family);
    }
  } // Handle Google fonts since Web Font Loader has a special module for them.
  else if (fontType === 'google_font') {
      // Handle the font variants
      // If there is a selected font variant and we haven't been instructed to load all, load only that,
      // otherwise load all the available variants.
      var _variants = typeof font.font_variant !== 'undefined' && (typeof fontConfig['fields']['font-weight']['loadAllVariants'] === 'undefined' || !fontConfig['fields']['font-weight']['loadAllVariants']) && typeof fontDetails.variants !== 'undefined' // If the font has no variants, any variant value we may have received should be ignored.
      && external_lodash_default().includes(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
      ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : [];

      if (!external_lodash_default().isEmpty(_variants)) {
        _variants = standardizeToArray(_variants);

        if (!external_lodash_default().isEmpty(_variants)) {
          family = family + ':' + _variants.join(',');
        }
      }

      if (fontsCache.indexOf(family) === -1) {
        WebFont.load({
          google: {
            families: [family]
          },
          classes: false,
          events: false
        }); // Remember we've loaded this family (with it's variants) so we don't load it again.

        fontsCache.push(family);
      }
    } else {// Maybe Typekit, Fonts.com or Fontdeck fonts
    }
}; // This is a mirror logic of the server-side Utils\Fonts::getFontFamilyFallbackStack()

var getFontFamilyFallbackStack = function getFontFamilyFallbackStack(fontFamily) {
  var fallbackStack = '';
  var fontDetails = parent.sm.customizer.getFontDetails(fontFamily);

  if (typeof fontDetails.fallback_stack !== 'undefined' && !external_lodash_default().isEmpty(fontDetails.fallback_stack)) {
    fallbackStack = fontDetails.fallback_stack;
  } else if (typeof fontDetails.category !== 'undefined' && !external_lodash_default().isEmpty(fontDetails.category)) {
    var category = fontDetails.category; // Search in the available categories for a match.

    if (typeof customify.fonts.categories[category] !== 'undefined') {
      // Matched by category ID/key
      fallbackStack = typeof customify.fonts.categories[category].fallback_stack !== 'undefined' ? customify.fonts.categories[category].fallback_stack : '';
    } else {
      // We need to search for aliases.
      external_lodash_default().find(customify.fonts.categories, function (categoryDetails) {
        if (typeof categoryDetails.aliases !== 'undefined') {
          var aliases = maybeImplodeList(categoryDetails.aliases);

          if (aliases.indexOf(category) !== -1) {
            // Found it.
            fallbackStack = typeof categoryDetails.fallback_stack !== 'undefined' ? categoryDetails.fallback_stack : '';
            return true;
          }
        }

        return false;
      });
    }
  }

  return fallbackStack;
}; // Mirror logic of server-side Utils\Fonts::sanitizeFontFamilyCSSValue()


var sanitizeFontFamilyCSSValue = function sanitizeFontFamilyCSSValue(value) {
  // Since we might get a stack, attempt to treat is a comma-delimited list.
  var fontFamilies = maybeExplodeList(value);

  if (!fontFamilies.length) {
    return '';
  }

  external_lodash_default().each(fontFamilies, function (fontFamily, key) {
    // Make sure that the font family is free from " or ' or whitespace, at the front.
    fontFamily = fontFamily.replace(new RegExp(/^\s*["'‘’“”]*\s*/), ''); // Make sure that the font family is free from " or ' or whitespace, at the back.

    fontFamily = fontFamily.replace(new RegExp(/\s*["'‘’“”]*\s*$/), '');

    if ('' === fontFamily) {
      delete fontFamilies[key];
      return;
    } // Now, if the font family contains spaces, wrap it in ".


    if (fontFamily.indexOf(' ') !== -1) {
      fontFamily = '"' + fontFamily + '"';
    } // Finally, put it back.


    fontFamilies[key] = fontFamily;
  });

  return maybeImplodeList(fontFamilies);
};

var standardizeToArray = function standardizeToArray(value) {
  if (typeof value === 'string' || typeof value === 'number') {
    value = [value];
  } else if (_typeof(value) === 'object') {
    value = Object.values(value);
  }

  return value;
};

var maybeExplodeList = function maybeExplodeList(str) {
  var delimiter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : ',';

  if (_typeof(str) === 'object') {
    str = standardizeToArray(str);
  } // If by any chance we are given an array, just return it


  if (Array.isArray(str)) {
    return str;
  } // Anything else we coerce to a string


  if (typeof str !== 'string') {
    str = String(str);
  } // Make sure we trim it


  str = str.trim(); // Bail on empty string

  if (!str.length) {
    return [];
  } // Return the whole string as an element if the delimiter is missing


  if (str.indexOf(delimiter) === -1) {
    return [str];
  } // Explode it and return it


  return explode(delimiter, str);
};

var maybeImplodeList = function maybeImplodeList(value) {
  var glue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : ',';

  // If by any chance we are given a string, just return it
  if (typeof value === 'string' || typeof value === 'number') {
    return String(value);
  }

  if (_typeof(value) === 'object') {
    value = standardizeToArray(value);
  }

  if (Array.isArray(value)) {
    return implode(glue, value);
  } // For anything else we return an empty string.


  return '';
};

var explode = function explode(delimiter, string, limit) {
  //  discuss at: https://locutus.io/php/explode/
  // original by: Kevin van Zonneveld (https://kvz.io)
  //   example 1: explode(' ', 'Kevin van Zonneveld')
  //   returns 1: [ 'Kevin', 'van', 'Zonneveld' ]
  if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined') {
    return null;
  }

  if (delimiter === '' || delimiter === false || delimiter === null) {
    return false;
  }

  if (typeof delimiter === 'function' || _typeof(delimiter) === 'object' || typeof string === 'function' || _typeof(string) === 'object') {
    return {
      0: ''
    };
  }

  if (delimiter === true) {
    delimiter = '1';
  } // Here we go...


  delimiter += '';
  string += '';
  var s = string.split(delimiter);
  if (typeof limit === 'undefined') return s; // Support for limit

  if (limit === 0) limit = 1; // Positive limit

  if (limit > 0) {
    if (limit >= s.length) {
      return s;
    }

    return s.slice(0, limit - 1).concat([s.slice(limit - 1).join(delimiter)]);
  } // Negative limit


  if (-limit >= s.length) {
    return [];
  }

  s.splice(s.length + limit);
  return s;
};

var implode = function implode(glue, pieces) {
  //  discuss at: https://locutus.io/php/implode/
  // original by: Kevin van Zonneveld (https://kvz.io)
  // improved by: Waldo Malqui Silva (https://waldo.malqui.info)
  // improved by: Itsacon (https://www.itsacon.net/)
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //   example 1: implode(' ', ['Kevin', 'van', 'Zonneveld'])
  //   returns 1: 'Kevin van Zonneveld'
  //   example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'})
  //   returns 2: 'Kevin van Zonneveld'
  var i = '';
  var retVal = '';
  var tGlue = '';

  if (arguments.length === 1) {
    pieces = glue;
    glue = '';
  }

  if (_typeof(pieces) === 'object') {
    if (Object.prototype.toString.call(pieces) === '[object Array]') {
      return pieces.join(glue);
    }

    for (i in pieces) {
      retVal += tGlue + pieces[i];
      tGlue = glue;
    }

    return retVal;
  }

  return pieces;
};
;// CONCATENATED MODULE: ./src/_js/customizer-preview/index.js


;

(function ($, window, document) {
  var _parent, _parent2;

  $(window).on('load', function () {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript();
  });
  window.fontsCache = [];
  window.wp = (window === null || window === void 0 ? void 0 : window.wp) || ((_parent = parent) === null || _parent === void 0 ? void 0 : _parent.wp);
  window.customify = (window === null || window === void 0 ? void 0 : window.customify) || ((_parent2 = parent) === null || _parent2 === void 0 ? void 0 : _parent2.customify);
  $(function () {
    var settings = customify.config.settings;

    var getStyleTagID = function getStyleTagID(settingID) {
      return "dynamic_style_".concat(settingID.replace(/\\W/g, '_'));
    };

    var properKeys = Object.keys(settings).filter(function (settingID) {
      var setting = settings[settingID];
      return setting.type === 'font' || Array.isArray(setting.css) && setting.css.length;
    });
    properKeys.forEach(function (settingID) {
      var style = document.createElement('style');
      var idAttr = getStyleTagID(settingID);
      style.setAttribute('id', idAttr);
      document.body.appendChild(style);
    }); // we create a queue of settingID => newValue pairs

    var updateQueue = {}; // so we can update their respective style tags in only one pass
    // and avoid multiple "recalculate styles" and all changes will appear
    // at the same time in the customizer preview

    var onChange = _.debounce(function () {
      var queue = Object.assign({}, updateQueue);
      updateQueue = {};
      Object.keys(queue).forEach(function (settingID) {
        var idAttr = getStyleTagID(settingID);
        var style = document.getElementById(idAttr);
        var newValue = queue[settingID];
        var settingConfig = settings[settingID];
        style.innerHTML = getSettingCSS(settingID, newValue, settingConfig);
      });
    }, 100);

    properKeys.forEach(function (settingID) {
      wp.customize(settingID, function (setting) {
        setting.bind(function (newValue) {
          updateQueue[settingID] = newValue;
          onChange();
        });
      });
    });
  });
})(jQuery, window, document);

var maybeLoadWebfontloaderScript = function maybeLoadWebfontloaderScript() {
  if (typeof WebFont === 'undefined') {
    var tk = document.createElement('script');
    tk.src = parent.customify.config.webfontloader_url;
    tk.type = 'text/javascript';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(tk, s);
  }
};

var defaultCallbackFilter = function defaultCallbackFilter(value, selector, property) {
  var unit = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
  return "".concat(selector, " { ").concat(property, ": ").concat(value).concat(unit, "; }");
};

var getSettingCSS = function getSettingCSS(settingID, newValue, settingConfig) {
  if (settingConfig.type === 'font') {
    maybeLoadFontFamily(newValue, settingID);
    var cssValue = getFontFieldCSSValue(settingID, newValue);
    return getFontFieldCSSCode(settingID, cssValue, newValue);
  }

  if (!Array.isArray(settingConfig.css)) {
    return '';
  }

  return settingConfig.css.reduce(function (acc, propertyConfig, index) {
    var callback_filter = propertyConfig.callback_filter,
        selector = propertyConfig.selector,
        property = propertyConfig.property,
        unit = propertyConfig.unit;
    var settingCallback = callback_filter && typeof window[callback_filter] === "function" ? window[callback_filter] : defaultCallbackFilter;

    if (!selector || !property) {
      return acc;
    }

    return "".concat(acc, "\n      ").concat(settingCallback(newValue, selector, property, unit));
  }, '');
};
}();
(this.sm = this.sm || {}).customizerPreview = __webpack_exports__;
/******/ })()
;