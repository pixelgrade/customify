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
/******/ 	return __webpack_require__(__webpack_require__.s = 10);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */,
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

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
    var nonce =  true ? __webpack_require__.nc : undefined;

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
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

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
/* 7 */,
/* 8 */,
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(6);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.i, ":root{--customizer-spacing: 20px;--customizer-field-border-width: 2px;--customizer-field-border-color: #DFE8EF;--customizer-field-focus-border-color: #48A9D7;--customizer-field-border-radius: 4px;--customizer-field-background-color: #FFFFFF;--customizer-field-text-color: #416B7E;--customizer-select-focus-background-color: #85C4EA;--customizer-field-height: 44px;--customizer-checkbox-size: 22px}.customize-control{width:auto;float:none;margin-bottom:0}.customize-control+.customize-control{margin-top:20px}.customize-control-title{margin-bottom:10px}.customize-control-description{margin-bottom:10px;font-style:normal;opacity:.75;clear:both}#customize-theme-controls li.customize-control-title{margin-bottom:0}#customize-theme-controls li.customize-control-title+.customize-control-checkbox,#customize-theme-controls li.customize-control-title+.customize-control-radio{margin-top:0}#customize-theme-controls .customize-control-textarea .customize-control-description{margin-top:initial}#customize-theme-controls .control-section.open{border-bottom:0;min-height:100%}.customize-control input[type=text]:not(#_customize-input-wpcom_custom_css_content_width_control):not(.wp-color-picker),.customize-control input[type=password],.customize-control input[type=date],.customize-control input[type=datetime],.customize-control input[type=datetime-local],.customize-control input[type=email],.customize-control input[type=month],.customize-control input[type=number],.customize-control input[type=tel],.customize-control input[type=time],.customize-control input[type=url],.customize-control input[type=week],.customize-control input[type=search]{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0}.customize-control input[type=text]:not(#_customize-input-wpcom_custom_css_content_width_control):not(.wp-color-picker):focus,.customize-control input[type=password]:focus,.customize-control input[type=date]:focus,.customize-control input[type=datetime]:focus,.customize-control input[type=datetime-local]:focus,.customize-control input[type=email]:focus,.customize-control input[type=month]:focus,.customize-control input[type=number]:focus,.customize-control input[type=tel]:focus,.customize-control input[type=time]:focus,.customize-control input[type=url]:focus,.customize-control input[type=week]:focus,.customize-control input[type=search]:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}.customize-control textarea{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0}.customize-control textarea:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}.customize-control select,.customize-control.customize-control-select select{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0;max-width:100%;-moz-appearance:none;-webkit-appearance:none}.customize-control select:focus,.customize-control.customize-control-select select:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}.customize-control select:not([multiple]),.customize-control.customize-control-select select:not([multiple]){padding-right:44px;background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxNSA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImFycm93IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iIzk4QzZERSIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICA8cG9seWdvbiBpZD0iYXJyb3ctc2hhcGUiIHBvaW50cz0iMTMuNDgxMiAwIDE1IDEuNTE0IDcuNSA5IDAgMS41MTQgMS41MTg4IDAgNy41IDUuOTY3MiI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\");background-repeat:no-repeat;background-position:right 16px top 16px}.customize-control select::-ms-expand,.customize-control.customize-control-select select::-ms-expand{display:none}.customize-control input[type=range]{position:relative;height:22px;overflow:hidden;outline:none;background:none;-webkit-appearance:none;-moz-appearange:none}.customize-control input[type=range]::-webkit-slider-runnable-track{width:100%;height:6px;background:#b8daeb;border-radius:999em}.customize-control input[type=range]::-moz-range-track{width:100%;height:6px;background:#b8daeb;border-radius:999em}.customize-control input[type=range]::-webkit-slider-thumb{position:relative;z-index:3;box-sizing:border-box;width:22px;height:22px;margin-top:-8px;border:2px solid #dfe8ef;border-radius:4px;background:#fff;cursor:move;cursor:grab;-webkit-appearance:none}.customize-control input[type=range]::-moz-range-thumb{position:relative;z-index:3;box-sizing:border-box;width:22px;height:22px;margin-top:-8px;border:2px solid #dfe8ef;border-radius:4px;background:#fff;cursor:move;cursor:grab;-moz-appearance:none}.customize-control input[type=range]:active::-webkit-slider-thumb{cursor:grabbing}.customize-control input[type=range]:active::-moz-range-thumb{cursor:grabbing}.customize-control-checkbox .customize-inside-control-row,.customize-control-radio .customize-inside-control-row{margin-left:0}.customize-control-checkbox input[type=checkbox],.customize-control-radio input[type=radio]{display:none}.customize-control-checkbox input[type=checkbox]+label,.customize-control-radio input[type=radio]+label{display:flex;align-items:center}.customize-control-checkbox input[type=checkbox]+label:before,.customize-control-radio input[type=radio]+label:before{content:\"\";display:block;width:var(--customizer-checkbox-size);height:var(--customizer-checkbox-size);flex:0 0 auto;margin-right:calc( 0.5 * var(--customizer-spacing) );border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);background:var(--customizer-field-background-color) center no-repeat}.customize-control-checkbox input[type=checkbox]:focus+label:before,.customize-control-radio input[type=radio]:focus+label:before{border-color:var(--customizer-field-focus-border-color)}.customize-control-checkbox input[type=checkbox]:checked+label:before,.customize-control-radio input[type=radio]:checked+label:before{border-color:var(--customizer-field-focus-border-color);background-color:var(--customizer-field-focus-border-color);background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTJweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxMiA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImNoZWNrIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8cG9seWdvbiBmaWxsPSIjRkZGRkZGIiBwb2ludHM9IjUgOSAxMiAyIDEwIDAgNSA1IDIgMiAtMS43NTkzMjk4M2UtMTUgNCI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\")}.customize-control-checkbox input[type=checkbox]+label:before{border-radius:4px}.customize-control-radio input[type=radio]+label:before{border-radius:50%}.customize-control-radio#customize-control-changeset_status .customize-inside-control-row{margin-top:0;text-indent:0}.customize-control-font:last-child{margin-bottom:150px}.font-options__wrapper{position:relative}.font-options__wrapper:after{content:\"\";position:absolute;top:90%;left:0;right:0;z-index:0;display:block;height:30px}.font-options__wrapper .customize-control-range>label:first-child{flex-basis:100%}.font-options__head{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0;padding-right:44px;background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxNSA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImFycm93IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iIzk4QzZERSIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICA8cG9seWdvbiBpZD0iYXJyb3ctc2hhcGUiIHBvaW50cz0iMTMuNDgxMiAwIDE1IDEuNTE0IDcuNSA5IDAgMS41MTQgMS41MTg4IDAgNy41IDUuOTY3MiI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\");background-repeat:no-repeat;background-position:right 16px top 16px;display:flex;align-items:baseline;justify-content:space-between;text-overflow:ellipsis;white-space:nowrap;-webkit-appearance:none}.font-options__head:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}.font-options__head:hover{border-color:var(--customizer-field-focus-border-color);background-color:var(--customizer-select-focus-background-color);background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxNSA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImFycm93IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iI0ZGRkZGRiIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICA8cG9seWdvbiBpZD0iYXJyb3ctc2hhcGUiIHBvaW50cz0iMTMuNDgxMiAwIDE1IDEuNTE0IDcuNSA5IDAgMS41MTQgMS41MTg4IDAgNy41IDUuOTY3MiI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\");color:#fff}.font-options__head .font-options__option-title{margin-right:10px}.font-options__checkbox:checked~.font-options__head{border-color:var(--customizer-field-focus-border-color);background-color:var(--customizer-select-focus-background-color);background-image:url(\"data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjkiIHdpZHRoPSIxNSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJtLTEtMWg1ODJ2NDAyaC01ODJ6IiBmaWxsPSJub25lIi8+PHBhdGggZD0ibTEzLjQ4MTIgMCAxLjUxODggMS41MTQtNy41IDcuNDg2LTcuNS03LjQ4NiAxLjUxODgtMS41MTQgNS45ODEyIDUuOTY3MnoiIGZpbGw9IiNmZmYiIHRyYW5zZm9ybT0ibWF0cml4KC0xIDAgMCAtMSAxNSA5KSIvPjwvc3ZnPg==\");color:#fff}.font-options__checkbox:not(:checked)~.font-options__head:not(:hover) .font-options__font-title{color:#98c6dd}.font-options__font-title{font-size:12px;line-height:20px;font-weight:300;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.font-options__option[class]{margin-bottom:0}.font-options__option+.font-options__option{margin-top:12px}.font-options__option label{display:block;margin-bottom:6px}input.font-options__checkbox{display:none}.font-options__options-list{position:absolute;top:100%;left:-12px;right:-12px;z-index:10;display:block;padding:10px 10px 14px;margin-top:5px;border:1px solid #b8daeb;border-radius:4px;box-shadow:0 5px 10px 0 rgba(0,0,0,.125);background-color:#fff}.font-options__options-list:before,.font-options__options-list:after{content:\"\";position:absolute;bottom:100%;right:26px;border:10px solid transparent}.font-options__options-list:before{z-index:100;border-bottom-color:#b8daeb;border-width:11px}.font-options__options-list:after{z-index:101;border-bottom-color:#f7fbff;margin-right:1px}.font-options__options-list label{font-size:13px}.font-options__checkbox:not(:checked)~.font-options__options-list{display:none}.customize-control-font-palette .customize-inside-control-row{position:relative;padding-top:52%;margin-left:0;overflow:hidden;background-color:#fff;background-size:contain;background-repeat:no-repeat;background-position:right center;border-radius:4px}.customize-control-font-palette .customize-inside-control-row+.customize-inside-control-row{margin-top:15px}.customize-control-font-palette .customize-inside-control-row input{display:none}.customize-control-font-palette .customize-inside-control-row input+label{position:absolute;top:0;right:0;bottom:0;left:0;border:2px solid #dfe8ef;border-radius:inherit}.customize-control-font-palette .customize-inside-control-row:hover input+label{border-color:#b8daeb}.customize-control-font-palette .customize-inside-control-row input:checked+label{border-color:#48a9d7}[id=sub-accordion-section-sm_font_palettes_section] [id=customize-control-sm_current_font_palette_control]~*{display:none !important}[id=sub-accordion-section-sm_font_palettes_section].sm-view-palettes [id=customize-control-sm_font_palette_control]{display:block !important}[id=sub-accordion-section-sm_font_palettes_section].sm-view-advanced [id=customize-control-sm_font_primary_control],[id=sub-accordion-section-sm_font_palettes_section].sm-view-advanced [id=customize-control-sm_font_secondary_control],[id=sub-accordion-section-sm_font_palettes_section].sm-view-advanced [id=customize-control-sm_font_accent_control],[id=sub-accordion-section-sm_font_palettes_section].sm-view-advanced [id=customize-control-sm_font_body_control]{display:block !important}.sm-radio-group{position:relative;border-radius:999em;overflow:hidden;height:2.428em;display:flex;flex-wrap:nowrap;align-items:stretch;color:#416b7e;font-size:14px;-webkit-font-smoothing:antialiased;font-weight:600;line-height:1.142em}.sm-radio-group>*{flex:1 1 0}.sm-radio-group:after{content:\"\";position:absolute;top:0;right:0;bottom:0;left:0;border:.142em solid #48a9d7;border-radius:999em;pointer-events:none}.sm-radio-group input[type=radio]{display:none}.sm-radio-group input[type=radio]:checked+label{background-color:#48a9d7;color:#fff}.sm-radio-group input[type=radio]:checked+label:before{content:\"\";display:inline-block;color:inherit;position:relative;top:.285em;margin-right:.45em;height:.8em;width:.55em;border-right:2px solid;border-bottom:2px solid;transform:translateY(-50%) rotate(45deg)}.sm-radio-group label{display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer;transition:all .25s ease;color:inherit}.sm-radio-group label:hover{color:#416b7e}.sm-radio-group label:nth-of-type(2){border-left:2px solid #48a9d7;border-right:2px solid #48a9d7}[id=customize-control-sm_coloration_level_control] .sm-radio-group{display:flex;flex-direction:row-reverse;flex-wrap:nowrap;align-items:stretch;justify-content:flex-start}[id=customize-control-sm_coloration_level_control] .sm-radio-group>*{flex:0 0 33.33%}[id=customize-control-sm_coloration_level_control] .sm-radio-group input[type=radio]:last-of-type:checked+label{background:linear-gradient(90deg, #3DAAE0 0%, #D557BE 100%)}[id=customize-control-sm_coloration_level_control] .sm-radio-group input[type=radio]:nth-of-type(1):checked~label:last-of-type,[id=customize-control-sm_coloration_level_control] .sm-radio-group input[type=radio]:nth-of-type(2):checked~label:last-of-type{margin-right:-33.33%}[id=customize-control-sm_coloration_level_control] .sm-radio-group label:nth-of-type(1){order:4}[id=customize-control-sm_coloration_level_control] .sm-radio-group label:nth-of-type(2){order:3;border-left:2px solid #48a9d7;border-right:2px solid #48a9d7}[id=customize-control-sm_coloration_level_control] .sm-radio-group label:nth-of-type(3){order:2}[id=customize-control-sm_coloration_level_control] .sm-radio-group label:nth-of-type(4){order:1;border-left:2px solid #48a9d7}.sm-radio-group+.description{margin-top:10px;margin-bottom:0}.customify_radio_html{display:flex;flex-wrap:wrap;align-items:stretch;margin-top:calc( -0.5 * var(--customizer-spacing) );margin-left:calc( -0.5 * var(--customizer-spacing) )}.customify_radio_html label{display:block;padding-top:var(--customizer-spacing);padding-left:var(--customizer-spacing);flex:0 0 50%}.customify_radio_html label>input+div{border-color:var(--customizer-field-border-color);height:100%}.customify_radio_html label:hover>input+div{background-color:var(--customizer-field-background-color)}.customify_radio_html input{display:none}.customify_radio_html input+div{display:flex;align-items:center;justify-content:center;border:var(--customizer-field-border-width) solid transparent;border-radius:var(--customizer-field-border-radius);padding:var(--customizer-spacing)}.customify_radio_html input:checked+div{border-color:var(--customizer-field-focus-border-color)}.customize-control-sm_switch{display:flex;align-items:center;justify-content:space-between}.customize-control-sm_switch .customize-control-title{margin-bottom:0}.sm-switch{display:flex;flex-wrap:nowrap;align-items:stretch;justify-content:flex-start;overflow:hidden;position:relative;height:2.428em;max-width:5.5em;border-radius:999em;color:#fff;font-size:14px;-webkit-font-smoothing:antialiased;font-weight:600;line-height:1.142em;z-index:10}.sm-switch input[type=radio]{display:none}.sm-switch input[type=radio]+label{display:flex;align-items:center;flex:0 0 100%}.sm-switch input[type=radio]+label,.sm-switch input[type=radio]+label:after{transition:all .2s ease-out}.sm-switch input[type=radio]:checked+label{pointer-events:none}.sm-switch input[type=radio]:nth-of-type(1)+label{padding-left:1.214em}.sm-switch input[type=radio]:nth-of-type(1)+label:after{content:\"\";position:absolute;top:0;right:0;bottom:0;left:0;background-color:#48a9d7;z-index:-1}.sm-switch input[type=radio]:nth-of-type(1):not(:checked)+label{transform:translateX(-100%) translateX(2.125em)}.sm-switch input[type=radio]:nth-of-type(1):not(:checked)+label:after{background:#ccc;transform:translateX(100%) translateX(-2.125em)}.sm-switch input[type=radio]:nth-of-type(1):not(:checked)+label~label{transform:translateX(-100%)}.sm-switch input[type=radio]:nth-of-type(1):checked+label~label{transform:translateX(-2.4em)}.sm-switch input[type=radio]:nth-of-type(1):checked+label~label:before{transform:translateX(-100%) translateX(2.125em)}.sm-switch input[type=radio]:nth-of-type(2)+label{flex-direction:row-reverse;padding-right:1em}.sm-switch input[type=radio]:nth-of-type(2)+label:before{content:\"\";position:absolute;top:0;right:0;bottom:0;left:0}.sm-switch input[type=radio]:nth-of-type(2)+label:after{content:\"\";display:block;height:2.142em;width:2.142em;margin-right:auto;margin-left:.125em;border-radius:50%;background-color:#fff;box-shadow:1px 2px 2px 0 rgba(23,21,21,.15);pointer-events:none}.sm-tabs{display:flex;justify-content:space-between;padding-left:8px;padding-right:16px;text-align:center;border-bottom:1px solid #dfe8ef}.sm-tabs__item{position:relative;padding:12px 0;margin-left:8px;margin-bottom:-1px;flex:1 1 0;font-size:14px;color:#416b7e;opacity:.7;cursor:pointer;transition:color .2s ease}.sm-tabs__item:after{content:\"\";position:absolute;bottom:0;left:0;width:100%;height:3px;transform-origin:50% 100%;transition:all .2s ease}.sm-tabs__item:not(.sm-tabs__item--active):not(:hover):after{transform:scaleY(0)}.sm-tabs__item:not(.sm-tabs__item--active):hover:after{background-color:#b8daeb}.sm-tabs__item--active{color:#2a3b44}.sm-tabs__item--active:after{background-color:#48a9d7}#accordion-section-customify-customizer-search{margin-bottom:0;color:#555d66;background:#fff;border-top:1px solid #ddd}#accordion-section-customify-customizer-search .accordion-section-title:after{content:none}#accordion-section-customify-customizer-search .search-field-wrapper{display:flex;flex-direction:row;justify-content:flex-start}#accordion-section-customify-customizer-search .search-field-wrapper .search-field-button-wrapper{display:flex;align-items:stretch}#accordion-section-customify-customizer-search .search-field-wrapper .clear-search{margin-left:6px;height:36px;font-weight:500}#accordion-section-customify-customizer-search .search-field-wrapper .clear-search.has-next-sibling{border-radius:3px 0 0 3px}#accordion-section-customify-customizer-search .search-field-wrapper .close-search{text-indent:0;border-radius:0 3px 3px 0;padding-left:0;padding-right:0;font-size:19px;height:36px;width:38px;float:left;transform:none;margin-top:0;line-height:2}#accordion-section-customify-customizer-search .search-field-wrapper .button-primary.has-next-sibling{border-right:1px solid #98c6dd}#accordion-section-customify-customizer-search .customizer-search-input{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0;width:83%;height:38px;padding:8px 8px}#accordion-section-customify-customizer-search .customizer-search-input:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}#accordion-section-customify-customizer-search input::-webkit-input-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}#accordion-section-customify-customizer-search input:-moz-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}#accordion-section-customify-customizer-search input::-moz-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}#accordion-section-customify-customizer-search input:-ms-input-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}#accordion-section-customify-customizer-search .search-input-label{display:block;margin-bottom:8px}#customize-controls .customize-info .customize-search-toggle{padding:20px;position:absolute;bottom:4px;right:1px;width:20px;height:20px;cursor:pointer;box-shadow:none;background:transparent;color:#555d66;border:none}#customize-controls .customize-info .customize-search-toggle:focus{outline:none}#customize-controls .customize-info .customize-search-toggle:before{padding:4px;position:absolute;top:5px;left:6px}#customize-controls .customize-info .customize-search-toggle:focus:before{border-radius:100%;box-shadow:0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8)}.search-found{height:0;visibility:hidden;opacity:0;display:none}#customify-search-results .accordion-section{border-left:none;border-right:none;padding:10px 10px 11px 14px;background:#fff;color:#416b7e;position:relative;font-weight:400;font-size:14px;line-height:21px;border-top:1px solid #dfe8ef;border-bottom:none}#customify-search-results .accordion-section:last-of-type{border-bottom:1px solid #dfe8ef}#customify-search-results .accordion-section:hover{background:#f7fbff;cursor:pointer}#customify-search-results .accordion-section:hover h3.accordion-section-title{background:#f7fbff}#customify-search-results .accordion-section:after{font:normal 20px/1 dashicons;speak:none;display:block;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-decoration:none !important;content:\"\";color:#a0a5aa;position:absolute;top:12px;right:10px;z-index:1;top:calc(50% - 10px)}#customify-search-results .accordion-section h3.accordion-section-title{padding:0;margin:0}#customify-search-results .accordion-section h3.accordion-section-title:after{content:none;background:inherit}#customify-search-results .accordion-section h3.accordion-section-title:hover{background:#f7fbff;cursor:pointer}.search-setting-path{cursor:pointer}#customize-controls .hl{background:#ffcd1724}.select2-container{box-sizing:border-box;display:inline-block;margin:0;position:relative;vertical-align:middle}.select2-container .select2-selection--single{box-sizing:border-box;cursor:pointer;display:block;height:28px;user-select:none;-webkit-user-select:none}.select2-container .select2-selection--single .select2-selection__rendered{display:block;padding-left:8px;padding-right:20px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.select2-container .select2-selection--single .select2-selection__clear{position:relative}.select2-container[dir=rtl] .select2-selection--single .select2-selection__rendered{padding-right:8px;padding-left:20px}.select2-container .select2-selection--multiple{box-sizing:border-box;cursor:pointer;display:block;min-height:32px;user-select:none;-webkit-user-select:none}.select2-container .select2-selection--multiple .select2-selection__rendered{display:inline-block;overflow:hidden;padding-left:8px;text-overflow:ellipsis;white-space:nowrap}.select2-container .select2-search--inline{float:left}.select2-container .select2-search--inline .select2-search__field{box-sizing:border-box;border:none;font-size:100%;margin-top:5px;padding:0}.select2-container .select2-search--inline .select2-search__field::-webkit-search-cancel-button{-webkit-appearance:none}.select2-dropdown{background-color:#fff;border:1px solid #aaa;border-radius:4px;box-sizing:border-box;display:block;position:absolute;left:-100000px;width:100%;z-index:1051}.select2-results{display:block}.select2-results__options{list-style:none;margin:0;padding:0}.select2-results__option{padding:6px;user-select:none;-webkit-user-select:none}.select2-results__option[aria-selected]{cursor:pointer}.select2-container--open .select2-dropdown{left:0}.select2-container--open .select2-dropdown--above{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0}.select2-container--open .select2-dropdown--below{border-top:none;border-top-left-radius:0;border-top-right-radius:0}.select2-search--dropdown{display:block;padding:4px}.select2-search--dropdown .select2-search__field{padding:4px;width:100%;box-sizing:border-box}.select2-search--dropdown .select2-search__field::-webkit-search-cancel-button{-webkit-appearance:none}.select2-search--dropdown.select2-search--hide{display:none}.select2-close-mask{border:0;margin:0;padding:0;display:block;position:fixed;left:0;top:0;min-height:100%;min-width:100%;height:auto;width:auto;opacity:0;z-index:99;background-color:#fff;filter:alpha(opacity=0)}.select2-hidden-accessible{border:0 !important;clip:rect(0 0 0 0) !important;-webkit-clip-path:inset(50%) !important;clip-path:inset(50%) !important;height:1px !important;overflow:hidden !important;padding:0 !important;position:absolute !important;width:1px !important;white-space:nowrap !important}.select2-container--default .select2-selection--single{background-color:#fff;border:1px solid #aaa;border-radius:4px}.select2-container--default .select2-selection--single .select2-selection__rendered{color:#444;line-height:28px}.select2-container--default .select2-selection--single .select2-selection__clear{cursor:pointer;float:right;font-weight:bold}.select2-container--default .select2-selection--single .select2-selection__placeholder{color:#999}.select2-container--default .select2-selection--single .select2-selection__arrow{height:26px;position:absolute;top:1px;right:1px;width:20px}.select2-container--default .select2-selection--single .select2-selection__arrow b{border-color:#888 transparent transparent transparent;border-style:solid;border-width:5px 4px 0 4px;height:0;left:50%;margin-left:-4px;margin-top:-2px;position:absolute;top:50%;width:0}.select2-container--default[dir=rtl] .select2-selection--single .select2-selection__clear{float:left}.select2-container--default[dir=rtl] .select2-selection--single .select2-selection__arrow{left:1px;right:auto}.select2-container--default.select2-container--disabled .select2-selection--single{background-color:#eee;cursor:default}.select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__clear{display:none}.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b{border-color:transparent transparent #888 transparent;border-width:0 4px 5px 4px}.select2-container--default .select2-selection--multiple{background-color:#fff;border:1px solid #aaa;border-radius:4px;cursor:text}.select2-container--default .select2-selection--multiple .select2-selection__rendered{box-sizing:border-box;list-style:none;margin:0;padding:0 5px;width:100%}.select2-container--default .select2-selection--multiple .select2-selection__rendered li{list-style:none}.select2-container--default .select2-selection--multiple .select2-selection__clear{cursor:pointer;float:right;font-weight:bold;margin-top:5px;margin-right:10px;padding:1px}.select2-container--default .select2-selection--multiple .select2-selection__choice{background-color:#e4e4e4;border:1px solid #aaa;border-radius:4px;cursor:default;float:left;margin-right:5px;margin-top:5px;padding:0 5px}.select2-container--default .select2-selection--multiple .select2-selection__choice__remove{color:#999;cursor:pointer;display:inline-block;font-weight:bold;margin-right:2px}.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover{color:#333}.select2-container--default[dir=rtl] .select2-selection--multiple .select2-selection__choice,.select2-container--default[dir=rtl] .select2-selection--multiple .select2-search--inline{float:right}.select2-container--default[dir=rtl] .select2-selection--multiple .select2-selection__choice{margin-left:5px;margin-right:auto}.select2-container--default[dir=rtl] .select2-selection--multiple .select2-selection__choice__remove{margin-left:2px;margin-right:auto}.select2-container--default.select2-container--focus .select2-selection--multiple{border:solid #000 1px;outline:0}.select2-container--default.select2-container--disabled .select2-selection--multiple{background-color:#eee;cursor:default}.select2-container--default.select2-container--disabled .select2-selection__choice__remove{display:none}.select2-container--default.select2-container--open.select2-container--above .select2-selection--single,.select2-container--default.select2-container--open.select2-container--above .select2-selection--multiple{border-top-left-radius:0;border-top-right-radius:0}.select2-container--default.select2-container--open.select2-container--below .select2-selection--single,.select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple{border-bottom-left-radius:0;border-bottom-right-radius:0}.select2-container--default .select2-search--dropdown .select2-search__field{border:1px solid #aaa}.select2-container--default .select2-search--inline .select2-search__field{background:transparent;border:none;outline:0;box-shadow:none;-webkit-appearance:textfield}.select2-container--default .select2-results>.select2-results__options{max-height:200px;overflow-y:auto}.select2-container--default .select2-results__option[role=group]{padding:0}.select2-container--default .select2-results__option[aria-disabled=true]{color:#999}.select2-container--default .select2-results__option[aria-selected=true]{background-color:#ddd}.select2-container--default .select2-results__option .select2-results__option{padding-left:1em}.select2-container--default .select2-results__option .select2-results__option .select2-results__group{padding-left:0}.select2-container--default .select2-results__option .select2-results__option .select2-results__option{margin-left:-1em;padding-left:2em}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-2em;padding-left:3em}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-3em;padding-left:4em}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-4em;padding-left:5em}.select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option{margin-left:-5em;padding-left:6em}.select2-container--default .select2-results__option--highlighted[aria-selected]{background-color:#5897fb;color:#fff}.select2-container--default .select2-results__group{cursor:default;display:block;padding:6px}.select2-container--classic .select2-selection--single{background-color:#f7f7f7;border:1px solid #dfe8ef;border-radius:4px;outline:0;background-image:-webkit-linear-gradient(top, white 50%, #eeeeee 100%);background-image:-o-linear-gradient(top, white 50%, #eeeeee 100%);background-image:linear-gradient(to bottom, white 50%, #eeeeee 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\"#FFFFFFFF\", endColorstr=\"#FFEEEEEE\", GradientType=0)}.select2-container--classic .select2-selection--single:focus{border:1px solid #48a9d7}.select2-container--classic .select2-selection--single .select2-selection__rendered{color:#444;line-height:28px}.select2-container--classic .select2-selection--single .select2-selection__clear{cursor:pointer;float:right;font-weight:bold;margin-right:10px}.select2-container--classic .select2-selection--single .select2-selection__placeholder{color:#999}.select2-container--classic .select2-selection--single .select2-selection__arrow{background-color:#ddd;border:none;border-left:1px solid #dfe8ef;border-top-right-radius:4px;border-bottom-right-radius:4px;height:26px;position:absolute;top:1px;right:1px;width:20px;background-image:-webkit-linear-gradient(top, #eeeeee 50%, #cccccc 100%);background-image:-o-linear-gradient(top, #eeeeee 50%, #cccccc 100%);background-image:linear-gradient(to bottom, #eeeeee 50%, #cccccc 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\"#FFEEEEEE\", endColorstr=\"#FFCCCCCC\", GradientType=0)}.select2-container--classic .select2-selection--single .select2-selection__arrow b{border-color:#888 transparent transparent transparent;border-style:solid;border-width:5px 4px 0 4px;height:0;left:50%;margin-left:-4px;margin-top:-2px;position:absolute;top:50%;width:0}.select2-container--classic[dir=rtl] .select2-selection--single .select2-selection__clear{float:left}.select2-container--classic[dir=rtl] .select2-selection--single .select2-selection__arrow{border:none;border-right:1px solid #dfe8ef;border-radius:0;border-top-left-radius:4px;border-bottom-left-radius:4px;left:1px;right:auto}.select2-container--classic.select2-container--open .select2-selection--single{border:1px solid #48a9d7}.select2-container--classic.select2-container--open .select2-selection--single .select2-selection__arrow{background:transparent;border:none}.select2-container--classic.select2-container--open .select2-selection--single .select2-selection__arrow b{border-color:transparent transparent #888 transparent;border-width:0 4px 5px 4px}.select2-container--classic.select2-container--open.select2-container--above .select2-selection--single{border-top:none;border-top-left-radius:0;border-top-right-radius:0;background-image:-webkit-linear-gradient(top, white 0%, #eeeeee 50%);background-image:-o-linear-gradient(top, white 0%, #eeeeee 50%);background-image:linear-gradient(to bottom, white 0%, #eeeeee 50%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\"#FFFFFFFF\", endColorstr=\"#FFEEEEEE\", GradientType=0)}.select2-container--classic.select2-container--open.select2-container--below .select2-selection--single{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0;background-image:-webkit-linear-gradient(top, #eeeeee 50%, white 100%);background-image:-o-linear-gradient(top, #eeeeee 50%, white 100%);background-image:linear-gradient(to bottom, #eeeeee 50%, white 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\"#FFEEEEEE\", endColorstr=\"#FFFFFFFF\", GradientType=0)}.select2-container--classic .select2-selection--multiple{background-color:#fff;border:1px solid #dfe8ef;border-radius:4px;cursor:text;outline:0}.select2-container--classic .select2-selection--multiple:focus{border:1px solid #48a9d7}.select2-container--classic .select2-selection--multiple .select2-selection__rendered{list-style:none;margin:0;padding:0 5px}.select2-container--classic .select2-selection--multiple .select2-selection__clear{display:none}.select2-container--classic .select2-selection--multiple .select2-selection__choice{background-color:#e4e4e4;border:1px solid #dfe8ef;border-radius:4px;cursor:default;float:left;margin-right:5px;margin-top:5px;padding:0 5px}.select2-container--classic .select2-selection--multiple .select2-selection__choice__remove{color:#888;cursor:pointer;display:inline-block;font-weight:bold;margin-right:2px}.select2-container--classic .select2-selection--multiple .select2-selection__choice__remove:hover{color:#555}.select2-container--classic[dir=rtl] .select2-selection--multiple .select2-selection__choice{float:right;margin-left:5px;margin-right:auto}.select2-container--classic[dir=rtl] .select2-selection--multiple .select2-selection__choice__remove{margin-left:2px;margin-right:auto}.select2-container--classic.select2-container--open .select2-selection--multiple{border:1px solid #48a9d7}.select2-container--classic.select2-container--open.select2-container--above .select2-selection--multiple{border-top:none;border-top-left-radius:0;border-top-right-radius:0}.select2-container--classic.select2-container--open.select2-container--below .select2-selection--multiple{border-bottom:none;border-bottom-left-radius:0;border-bottom-right-radius:0}.select2-container--classic .select2-search--dropdown .select2-search__field{border:1px solid #dfe8ef;outline:0}.select2-container--classic .select2-search--inline .select2-search__field{outline:0;box-shadow:none}.select2-container--classic .select2-dropdown{background-color:#fff;border:1px solid transparent}.select2-container--classic .select2-dropdown--above{border-bottom:none}.select2-container--classic .select2-dropdown--below{border-top:none}.select2-container--classic .select2-results>.select2-results__options{max-height:200px;overflow-y:auto}.select2-container--classic .select2-results__option[role=group]{padding:0}.select2-container--classic .select2-results__option[aria-disabled=true]{color:gray}.select2-container--classic .select2-results__option--highlighted[aria-selected]{background-color:#48a9d7;color:#fff}.select2-container--classic .select2-results__group{cursor:default;display:block;padding:6px}.select2-container--classic.select2-container--open .select2-dropdown{border-color:#48a9d7}.select2-container{color:#416b7e;z-index:999999}.select2-container .select2-search--dropdown{padding:6px}.select2-container .select2-search--dropdown .select2-search__field[class]{height:40px;padding:10px 8px}.select2-container ::-webkit-input-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}.select2-container :-moz-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}.select2-container ::-moz-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}.select2-container :-ms-input-placeholder{font-size:14px;color:#999;opacity:1;line-height:1.5}.select2-search .select2-search__field[class]{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0}.select2-search .select2-search__field[class]:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}.select2-results__option{padding-left:14px;padding-right:14px}#customize-theme-controls .select2-container{width:100% !important}#customize-theme-controls .select2-container.select2-container--focus .select2-selection--multiple{border-color:#48a9d7}#customize-theme-controls .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:inherit}#customize-theme-controls .select2-selection--single{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0;padding-right:44px;background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxNSA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImFycm93IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iIzk4QzZERSIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICA8cG9seWdvbiBpZD0iYXJyb3ctc2hhcGUiIHBvaW50cz0iMTMuNDgxMiAwIDE1IDEuNTE0IDcuNSA5IDAgMS41MTQgMS41MTg4IDAgNy41IDUuOTY3MiI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\");background-repeat:no-repeat;background-position:right 16px top 16px;height:auto}#customize-theme-controls .select2-selection--single:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}#customize-theme-controls .select2-selection--multiple{display:flex;align-items:center;width:100%;padding:.75em 1em;margin:0;border:var(--customizer-field-border-width) solid var(--customizer-field-border-color);border-radius:var(--customizer-field-border-radius);background:var(--customizer-field-background-color);color:var(--customizer-field-text-color);font-size:14px;font-weight:500;line-height:1.5;transition:all .2s ease-out;transition-property:color,background-color,border-color;outline:0;padding:8px 8px 2px}#customize-theme-controls .select2-selection--multiple:focus{border-color:var(--customizer-field-focus-border-color);box-shadow:none}#customize-theme-controls .select2-selection__rendered{color:inherit;padding-left:0;padding-right:0}#customize-theme-controls .select2-selection--single .select2-selection__rendered{padding-left:0;padding-right:0}#customize-theme-controls .select2-selection__choice{padding:2px 7px 1px;margin-right:6px;margin-top:0}#customize-theme-controls .select2-search__field{min-width:100%;height:29px;margin-top:0;border-width:0}#customize-theme-controls .select2-search--inline .select2-search__field{height:27px;padding:7px 0;min-height:auto}#customize-theme-controls .select2-selection--single:hover{border-color:var(--customizer-field-focus-border-color);background-color:var(--customizer-select-focus-background-color);background-image:url(\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSI5cHgiIHZpZXdCb3g9IjAgMCAxNSA5IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPGcgaWQ9ImFycm93IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iI0ZGRkZGRiIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICA8cG9seWdvbiBpZD0iYXJyb3ctc2hhcGUiIHBvaW50cz0iMTMuNDgxMiAwIDE1IDEuNTE0IDcuNSA5IDAgMS41MTQgMS41MTg4IDAgNy41IDUuOTY3MiI+PC9wb2x5Z29uPgogICAgPC9nPgo8L3N2Zz4=\");color:#fff}#customize-theme-controls .select2-container--open .select2-selection--single{border-color:var(--customizer-field-focus-border-color);background-color:var(--customizer-select-focus-background-color);background-image:url(\"data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjkiIHdpZHRoPSIxNSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJtLTEtMWg1ODJ2NDAyaC01ODJ6IiBmaWxsPSJub25lIi8+PHBhdGggZD0ibTEzLjQ4MTIgMCAxLjUxODggMS41MTQtNy41IDcuNDg2LTcuNS03LjQ4NiAxLjUxODgtMS41MTQgNS45ODEyIDUuOTY3MnoiIGZpbGw9IiNmZmYiIHRyYW5zZm9ybT0ibWF0cml4KC0xIDAgMCAtMSAxNSA5KSIvPjwvc3ZnPg==\");color:#fff;background-position:right 17px top 16px}#customize-theme-controls .select2-container--open .select2-selection--single{border-bottom-right-radius:0;border-bottom-left-radius:0}#customize-theme-controls .select2-selection__arrow{display:none}.select2-results__group[class]{padding:8px 14px;border-width:1px 0;border-style:solid;border-color:#dfe8ef;background:#f7fbff}.select2-results__group[class]+.select2-results__options{margin-top:6px}.select2-results__option{margin-bottom:0}.select2-results__option+.select2-results__option{margin-top:6px}.select2-results__option[aria-selected=true]{color:inherit !important;background:transparent !important;opacity:.3;pointer-events:none}.customify-color-select__option-list{display:flex;margin-left:-5px}.customify-color-select__option-list>*{border-radius:50%;overflow:hidden;background-color:currentColor;text-indent:-999em;flex:0 0 auto;width:30px;margin-left:5px;border:2px solid transparent;box-shadow:#fff 0 0 1px 2px inset;background-clip:content-box}.customify-color-select__option-list>*:before{content:\"\";padding-top:100%;display:block}.customify-color-select__option-label{display:none}.customify-color-select__option--text{color:#333}.customify-color-select__option--titles{color:#222}.customify-color-select__option--accent{color:#a22}.customify-color-select__option--background{color:#eee}.customify-color-select__option--selected{border-color:#28a}.customize-control-range{display:flex;flex-wrap:wrap;align-items:center;margin-left:-1em}.customize-control-range[class][class]>*{margin-left:1em}.customize-control-range .customize-control-title,.customize-control-range .customize-control-notifications-container{width:100%}.customize-control-range input[type=range]{flex-grow:1}.customize-control-range .range-value{flex-basis:5em}#customize-theme-controls #accordion-section-sm_color_palettes_section>.accordion-section-title{border-bottom:1px solid #dfe8ef}#customize-theme-controls #accordion-section-sm_color_palettes_section+#accordion-section-sm_font_palettes_section>.accordion-section-title{margin-top:5px;border-top-width:1px}#customize-theme-controls #accordion-section-sm_color_palettes_section>.accordion-section-title,#customize-theme-controls #accordion-section-sm_font_palettes_section>.accordion-section-title{display:flex;align-items:center;justify-content:space-between;justify-content:flex-start}#customize-theme-controls #accordion-section-sm_color_palettes_section>.accordion-section-title:before,#customize-theme-controls #accordion-section-sm_font_palettes_section>.accordion-section-title:before{font-family:dashicons;font-size:17px;-webkit-font-smoothing:antialiased}#customize-theme-controls #accordion-section-sm_color_palettes_section>.accordion-section-title:before,#customize-theme-controls #accordion-section-sm_font_palettes_section>.accordion-section-title:before{width:2em;height:2em;margin-right:15px;padding-left:.1em;display:flex;align-items:center;justify-content:center;text-align:center;background-color:#85c4ea;color:#f7fbff;border-radius:50%}#customize-theme-controls #accordion-section-sm_color_palettes_section>.accordion-section-title:before{content:\"\"}#customize-theme-controls #accordion-section-sm_font_palettes_section>.accordion-section-title:before{content:\"\"}.wp-full-overlay-sidebar,.wp-full-overlay-sidebar *,.wp-full-overlay-sidebar *:before,.wp-full-overlay-sidebar *:after{box-sizing:border-box}#customize-controls h3.customize-section-title,#customize-controls .customize-section-title h3{padding:10px 20px 11px 20px;color:#416b7e;background:#fff}#customize-theme-controls .accordion-section-title{font-size:14px;line-height:21px;padding:10px 40px 11px 20px;color:#416b7e;background:#fff}#customize-theme-controls h3.accordion-section-title{font-weight:normal}#customize-theme-controls .accordion-section>.accordion-section-title{border:0 solid #dfe8ef}#customize-theme-controls .accordion-section:last-of-type>.accordion-section-title{border-bottom:1px solid #dfe8ef}#customize-theme-controls .accordion-section:not(.panel-meta)>.accordion-section-title{border-top:1px solid #dfe8ef}#customize-theme-controls .accordion-section:not(.panel-meta)>.accordion-section-title:focus,#customize-theme-controls .accordion-section:not(.panel-meta):hover>.accordion-section-title{background:#f7fbff}#customize-theme-controls .panel-meta>.customize-control-notifications-container,#customize-theme-controls .customize-section-title>.customize-control-notifications-container{border-color:#dfe8ef}#customize-controls #customize-theme-controls .accordion-section[id*=theme_options_panel]>.accordion-section-title,#customize-controls #customize-theme-controls .accordion-section[id*=accordion-section-pro__section]>.accordion-section-title{border-bottom-width:1px;margin-bottom:10px}#customize-theme-controls #accordion-section-title_tagline .accordion-section-title,#customize-theme-controls #accordion-panel-style_manager_panel .accordion-section-title,#customize-theme-controls #accordion-panel-theme_options_panel .accordion-section-title{display:flex;align-items:center;justify-content:space-between}#customize-theme-controls #accordion-section-title_tagline .accordion-section-title:before,#customize-theme-controls #accordion-panel-style_manager_panel .accordion-section-title:before,#customize-theme-controls #accordion-panel-theme_options_panel .accordion-section-title:before{font-family:dashicons;font-size:17px;-webkit-font-smoothing:antialiased}#customize-theme-controls #accordion-section-title_tagline .accordion-section-title:before,#customize-theme-controls #accordion-panel-style_manager_panel .accordion-section-title:before,#customize-theme-controls #accordion-panel-theme_options_panel .accordion-section-title:before{color:#b8daeb;order:2}#customize-theme-controls #accordion-section-title_tagline>.accordion-section-title:before{content:\"\";color:#85c4ea}#customize-theme-controls #accordion-section-title_tagline>.accordion-section-title img{display:none !important}#customize-theme-controls #accordion-panel-style_manager_panel>.accordion-section-title:before{content:\"\";font-size:18px;color:#f8bc30}#customize-theme-controls #accordion-panel-theme_options_panel>.accordion-section-title:before{content:\"\"}#customize-save-button-wrapper{display:flex;align-items:stretch;margin-top:6px}.customize-controls-close{width:45px;height:45px;padding:0;border-top:0;color:#98c6dd;background:#fff;border-color:#dfe8ef}.customize-controls-close:focus,.customize-controls-close:hover{background:#f7fbff}.customize-controls-close:before{top:0}#customize-controls #customize-theme-controls .customize-info,#customize-controls #customize-theme-controls .customize-section-title{display:flex;flex-wrap:wrap;flex-grow:1}#customize-controls #customize-theme-controls .customize-info>.customize-control-notifications-container,#customize-controls #customize-theme-controls .customize-section-title>.customize-control-notifications-container{flex-basis:100%}#customize-controls #customize-theme-controls .customize-info>.accordion-section-title,#customize-controls #customize-theme-controls .customize-section-title>h3{flex-grow:1;margin-left:0;color:#2a3b44}#customize-controls #customize-theme-controls .customize-panel-back,#customize-controls #customize-theme-controls .customize-section-back{position:relative;width:45px;height:auto;padding:0;color:#98c6dd;border-color:#dfe8ef;border-left:0}#customize-controls #customize-theme-controls .customize-panel-back:hover,#customize-controls #customize-theme-controls .customize-panel-back:focus,#customize-controls #customize-theme-controls .customize-section-back:hover,#customize-controls #customize-theme-controls .customize-section-back:focus{background:#f7fbff}#customize-controls #customize-theme-controls .customize-panel-back:before,#customize-controls #customize-theme-controls .customize-section-back:before{position:absolute;top:50%;left:50%;transform:translate(-40%, -50%);line-height:1}.customize-controls-preview-toggle{background-color:#fff;border-color:#dfe8ef}.in-sub-panel .wp-full-overlay-sidebar .wp-full-overlay-header{padding-left:45px}.wp-full-overlay-sidebar .wp-full-overlay-header{height:46px}#customize-theme-controls .separator.label{display:block;font-size:14px;line-height:24px;font-weight:500}#customize-theme-controls .separator.large{margin-top:10px;font-size:16px;color:#2a3b44}#customize-theme-controls .separator.section:before,#customize-theme-controls .separator.sub-section:before{content:\"\";position:absolute;top:0;bottom:0;left:-20px;right:-20px;z-index:-1;border-width:1px 0;border-style:solid;border-color:#dfe8ef;background-color:#fff}#customize-theme-controls .separator.section+.customize-control-description,#customize-theme-controls .separator.sub-section+.customize-control-description{margin-top:20px}#customize-theme-controls .separator.section{position:relative;padding:14px 0;margin-bottom:0;background:none;border:none}#customize-theme-controls .separator.section:before{border-width:1px 0}#customize-theme-controls .separator.sub-section{position:relative;padding:12px 0}#customize-theme-controls .separator.sub-section:before{border-width:1px 0;background-color:#fff}.customize-control.menu-item .menu-item-settings{background-color:#f7fbff}#customize-theme-controls .control-panel-content:not(.control-panel-nav_menus) .control-section:nth-child(2),#customize-theme-controls .control-panel-nav_menus .control-section-nav_menu,#customize-theme-controls .control-section-nav_menu_locations .accordion-section-title{border-top:0}#customize-controls{background:#f7fbff;border-color:#dfe8ef}#customize-controls .customize-info{border-color:#dfe8ef}#customize-header-actions,#customize-footer-actions{background:#fff;border-color:#dfe8ef}#customize-theme-controls .customize-info .customize-panel-description,#customize-theme-controls .customize-info .customize-section-description,#customize-outer-theme-controls .customize-info .customize-section-description,#customize-theme-controls .no-widget-areas-rendered-notice{padding:20px;border-color:#dfe8ef;background:#fff;color:#2a3b44}#customize-theme-controls .customize-pane-child.accordion-section-content{padding:20px}.customize-section-title{margin:-20px;margin-bottom:0;border-color:#dfe8ef}.wp-full-overlay-sidebar-content .accordion-section-content{overflow:visible}", ""]);
// Exports
/* harmony default export */ __webpack_exports__["a"] = (___CSS_LOADER_EXPORT___);


/***/ }),
/* 10 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external "jQuery"
var external_jQuery_ = __webpack_require__(0);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);

// EXTERNAL MODULE: ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js
var injectStylesIntoStyleTag = __webpack_require__(5);
var injectStylesIntoStyleTag_default = /*#__PURE__*/__webpack_require__.n(injectStylesIntoStyleTag);

// EXTERNAL MODULE: ./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/js/customizer/style.scss
var style = __webpack_require__(9);

// CONCATENATED MODULE: ./src/js/customizer/style.scss

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = injectStylesIntoStyleTag_default()(style["a" /* default */], options);



/* harmony default export */ var customizer_style = (style["a" /* default */].locals || {});
// CONCATENATED MODULE: ./src/js/customizer/fields/color-select/index.js

var color_select_handleColorSelectFields = function handleColorSelectFields() {
  external_jQuery_default()('.js-color-select').each(function (i, obj) {
    color_select_convertToColorSelect(obj);
  });
};
var color_select_convertToColorSelect = function convertToColorSelect(element) {
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
// CONCATENATED MODULE: ./src/js/customizer/fields/range/index.js

var range_handleRangeFields = function handleRangeFields() {
  var rangeControlSelector = ".accordion-section-content[id*=\"".concat(customify.config.options_name, "\"], #sub-accordion-section-sm_color_palettes_section");
  external_jQuery_default()(rangeControlSelector).each(function (i, container) {
    var $rangeFields = external_jQuery_default()(container).find('input[type="range"]'); // For each range input add a number field (for preview mainly - but it can also be used for input)

    $rangeFields.each(function (i, obj) {
      var $range = external_jQuery_default()(obj);
      var $number = $range.siblings('.range-value');

      if (!$number.length) {
        $number = $range.clone();
        $number.attr('type', 'number').attr('class', 'range-value').removeAttr('data-value_entry');
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
// CONCATENATED MODULE: ./src/js/customizer/folding-fields.js

/**
 * This function will search for all the interdependend fields and make a bound between them.
 * So whenever a target is changed, it will take actions to the dependent fields.
 * @TODO  this is still written in a barbaric way, refactor when needed
 */

var folding_fields_handleFoldingFields = function handleFoldingFields() {
  if (_.isUndefined(customify.config) || _.isUndefined(customify.config.settings)) {
    return; // bail
  }

  external_jQuery_default.a.fn.reactor.defaults.compliant = function () {
    external_jQuery_default()(this).slideDown();
    external_jQuery_default()(this).find(':disabled').attr({
      disabled: false
    });
  };

  external_jQuery_default.a.fn.reactor.defaults.uncompliant = function () {
    external_jQuery_default()(this).slideUp();
    external_jQuery_default()(this).find(':enabled').attr({
      disabled: true
    });
  };

  var IS = external_jQuery_default.a.extend({}, external_jQuery_default.a.fn.reactor.helpers);

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

  external_jQuery_default.a.each(customify.config.settings, function (id, field) {
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
        external_jQuery_default.a.each(field.show_if, function (i, j) {
          bindFoldingEvents(parentID, j, relation);
        });
      }
    }
  });
};
// CONCATENATED MODULE: ./src/js/customizer/scale-preview.js

var scale_preview_scalePreview = function scalePreview() {
  wp.customize.previewer.bind('synced', function () {
    scale_preview_scalePreviewIframe();
    wp.customize.previewedDevice.bind(scale_preview_scalePreviewIframe);
    external_jQuery_default()(window).on('resize', scale_preview_scalePreviewIframe);
  });
  external_jQuery_default()('.collapse-sidebar').on('click', function () {
    setTimeout(scale_preview_scalePreviewIframe, 300);
  });
};

var scale_preview_scalePreviewIframe = function scalePreviewIframe() {
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
// CONCATENATED MODULE: ./src/js/customizer/utils.js
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
// CONCATENATED MODULE: ./src/js/customizer/reset.js


var reset_createResetButtons = function createResetButtons() {
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

  external_jQuery_default.a.each(api.settings.controls, function (key, ctrl) {
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
    external_jQuery_default.a.each(sections, function () {
      var controls = this.controls();

      if (controls.length > 0) {
        external_jQuery_default.a.each(controls, function (key, ctrl) {
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
    external_jQuery_default.a.each(controls, function (key, ctrl) {
      var setting_id = ctrl.id.replace('_control', ''),
          setting = customify.config.settings[setting_id];

      if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
        apiSetSettingValue(setting_id, setting.default);
      }
    });
  }
}
// CONCATENATED MODULE: ./src/js/customizer/index.js







wp.customize.bind('ready', function () {
  range_handleRangeFields();
  color_select_handleColorSelectFields(); // @todo check reason for this timeout

  setTimeout(function () {
    folding_fields_handleFoldingFields();
  }, 1000); // Initialize simple select2 fields.

  external_jQuery_default()('.customify_select2').select2(); // Initialize font fields.

  customify.fontFields.init();
  scale_preview_scalePreview();
});

/***/ })
/******/ ]);