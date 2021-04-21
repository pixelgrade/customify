/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/_js/dark-mode/index.js":
/*!************************************!*\
  !*** ./src/_js/dark-mode/index.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* binding */ DarkMode; }\n/* harmony export */ });\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ \"jquery\");\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }\n\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }\n\n\nvar COLOR_SCHEME_BUTTON = '.is-color-scheme-switcher-button';\nvar STORAGE_ITEM = 'color-scheme-dark';\nvar TEMP_STORAGE_ITEM = 'color-scheme-dark-temp';\nvar ignoreStorage = !!wp.customize;\n\nvar DarkMode = /*#__PURE__*/function () {\n  function DarkMode(element) {\n    _classCallCheck(this, DarkMode);\n\n    this.$element = jquery__WEBPACK_IMPORTED_MODULE_0___default()(element);\n    this.$html = jquery__WEBPACK_IMPORTED_MODULE_0___default()('html');\n    this.$colorSchemeButtons = jquery__WEBPACK_IMPORTED_MODULE_0___default()(COLOR_SCHEME_BUTTON);\n    this.$colorSchemeButtonsLink = this.$colorSchemeButtons.children('a');\n    this.matchMedia = window.matchMedia('(prefers-color-scheme: dark)');\n    this.darkModeSetting = this.$html.data('dark-mode-advanced');\n    this.theme = null;\n    this.initialize();\n  }\n\n  _createClass(DarkMode, [{\n    key: \"initialize\",\n    value: function initialize() {\n      localStorage.removeItem(TEMP_STORAGE_ITEM);\n      this.bindEvents();\n      this.bindCustomizer();\n      this.update();\n    }\n  }, {\n    key: \"bindEvents\",\n    value: function bindEvents() {\n      var _this = this;\n\n      jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).on('click', COLOR_SCHEME_BUTTON, this.onClick.bind(this));\n      this.matchMedia.addEventListener('change', function () {\n        localStorage.removeItem(TEMP_STORAGE_ITEM);\n\n        _this.update();\n      });\n    }\n  }, {\n    key: \"bindCustomizer\",\n    value: function bindCustomizer() {\n      var _this2 = this;\n\n      if (!wp.customize) {\n        return;\n      }\n\n      wp.customize.bind('ready', function () {\n        wp.customize('sm_dark_mode_advanced', function (setting) {\n          var _wp, _wp$customize;\n\n          localStorage.removeItem(TEMP_STORAGE_ITEM);\n          _this2.darkModeSetting = setting();\n\n          _this2.update();\n\n          setting.bind(function (newValue, oldValue) {\n            localStorage.removeItem(TEMP_STORAGE_ITEM);\n            _this2.darkModeSetting = newValue;\n\n            _this2.update();\n          });\n          var previewer = (_wp = wp) === null || _wp === void 0 ? void 0 : (_wp$customize = _wp.customize) === null || _wp$customize === void 0 ? void 0 : _wp$customize.previewer;\n\n          if (previewer) {\n            previewer.bind('ready', function () {\n              var targetWindow = previewer.preview.targetWindow();\n              _this2.$html = _this2.$html.add(targetWindow.document.documentElement);\n            });\n          }\n        });\n      });\n    }\n  }, {\n    key: \"onClick\",\n    value: function onClick(e) {\n      e.preventDefault();\n      var isDark = this.isCompiledDark();\n      localStorage.setItem(this.getStorageItemKey(), !!isDark ? 'light' : 'dark');\n      this.update();\n    }\n  }, {\n    key: \"getStorageItemKey\",\n    value: function getStorageItemKey() {\n      return !ignoreStorage ? STORAGE_ITEM : TEMP_STORAGE_ITEM;\n    }\n  }, {\n    key: \"isSystemDark\",\n    value: function isSystemDark() {\n      var isDark = this.darkModeSetting === 'on';\n\n      if (this.darkModeSetting === 'auto' && this.matchMedia.matches) {\n        isDark = true;\n      }\n\n      return isDark;\n    }\n  }, {\n    key: \"isCompiledDark\",\n    value: function isCompiledDark() {\n      var isDark = this.isSystemDark();\n      var colorSchemeStorageValue = localStorage.getItem(this.getStorageItemKey());\n\n      if (colorSchemeStorageValue !== null) {\n        isDark = colorSchemeStorageValue === 'dark';\n      }\n\n      return isDark;\n    }\n  }, {\n    key: \"update\",\n    value: function update() {\n      this.$html.toggleClass('is-dark', this.isCompiledDark());\n    }\n  }]);\n\n  return DarkMode;\n}();\n\n\nvar Dark = new DarkMode();\n\n//# sourceURL=webpack://sm.%5Bname%5D/./src/_js/dark-mode/index.js?");

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

module.exports = window["jQuery"];

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
/******/ 			// no module.id needed
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
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/_js/dark-mode/index.js");
/******/ 	(window.sm = window.sm || {}).darkMode = __webpack_exports__;
/******/ 	
/******/ })()
;