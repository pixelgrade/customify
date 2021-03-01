(this.sm = this.sm || {}).darkMode =
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 6521:
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ DarkMode; }
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(3609);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
var _window, _window$wp;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }


var COLOR_SCHEME_BUTTON = '.is-color-scheme-switcher-button';
var STORAGE_ITEM = 'color-scheme-dark';
var TEMP_STORAGE_ITEM = 'color-scheme-dark-temp';
var $html = jquery__WEBPACK_IMPORTED_MODULE_0___default()('html');
var api = (_window = window) === null || _window === void 0 ? void 0 : (_window$wp = _window.wp) === null || _window$wp === void 0 ? void 0 : _window$wp.customize;
var ignoreStorage = !!api;

var DarkMode = /*#__PURE__*/function () {
  function DarkMode(element) {
    _classCallCheck(this, DarkMode);

    this.$element = jquery__WEBPACK_IMPORTED_MODULE_0___default()(element);
    this.$html = jquery__WEBPACK_IMPORTED_MODULE_0___default()('html');
    this.$colorSchemeButtons = jquery__WEBPACK_IMPORTED_MODULE_0___default()(COLOR_SCHEME_BUTTON);
    this.$colorSchemeButtonsLink = this.$colorSchemeButtons.children('a');
    this.matchMedia = window.matchMedia('(prefers-color-scheme: dark)');
    this.darkModeSetting = $html.data('dark-mode-advanced');
    this.theme = null;
    this.initialize();
  }

  _createClass(DarkMode, [{
    key: "initialize",
    value: function initialize() {
      localStorage.removeItem(TEMP_STORAGE_ITEM);
      this.bindEvents();
      this.bindCustomizer();
      this.update();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;

      jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).on('click', COLOR_SCHEME_BUTTON, this.onClick.bind(this));
      this.matchMedia.addEventListener('change', function () {
        localStorage.removeItem(TEMP_STORAGE_ITEM);

        _this.update();
      });
    }
  }, {
    key: "bindCustomizer",
    value: function bindCustomizer() {
      var _this2 = this;

      if (!api) {
        return;
      }

      api.bind('ready', function () {
        var _wp, _wp$customize;

        var setting = api('sm_dark_mode_advanced');
        localStorage.removeItem(TEMP_STORAGE_ITEM);
        _this2.darkModeSetting = setting();

        _this2.update();

        setting.bind(function (newValue, oldValue) {
          localStorage.removeItem(TEMP_STORAGE_ITEM);
          _this2.darkModeSetting = newValue;

          _this2.update();
        });
        var previewer = (_wp = wp) === null || _wp === void 0 ? void 0 : (_wp$customize = _wp.customize) === null || _wp$customize === void 0 ? void 0 : _wp$customize.previewer;

        if (previewer) {
          previewer.bind('ready', function () {
            var targetWindow = previewer.preview.targetWindow();
            _this2.$html = _this2.$html.add(targetWindow.document.documentElement);
          });
        }
      });
    }
  }, {
    key: "onClick",
    value: function onClick(e) {
      e.preventDefault();
      var isDark = this.isCompiledDark();
      localStorage.setItem(this.getStorageItemKey(), !!isDark ? 'light' : 'dark');
      this.update();
    }
  }, {
    key: "getStorageItemKey",
    value: function getStorageItemKey() {
      return !ignoreStorage ? STORAGE_ITEM : TEMP_STORAGE_ITEM;
    }
  }, {
    key: "isSystemDark",
    value: function isSystemDark() {
      var isDark = this.darkModeSetting === 'on';

      if (this.darkModeSetting === 'auto' && this.matchMedia.matches) {
        isDark = true;
      }

      return isDark;
    }
  }, {
    key: "isCompiledDark",
    value: function isCompiledDark() {
      var isDark = this.isSystemDark();
      var colorSchemeStorageValue = localStorage.getItem(this.getStorageItemKey());

      if (colorSchemeStorageValue !== null) {
        isDark = colorSchemeStorageValue === 'dark';
      }

      return isDark;
    }
  }, {
    key: "update",
    value: function update() {
      console.log('aici');
      this.$html.toggleClass('is-dark', this.isCompiledDark());
    }
  }]);

  return DarkMode;
}();


var Dark = new DarkMode();
window.myApi = {};
window.myApi.isDark = Dark.isCompiledDark.bind(Dark);

/***/ }),

/***/ 3609:
/***/ (function(module) {

module.exports = (function() { return this["jQuery"]; }());

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
/******/ 	// module exports must be returned from runtime so entry inlining is disabled
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(6521);
/******/ })()
;