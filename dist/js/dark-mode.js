/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
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
/************************************************************************/

// UNUSED EXPORTS: default

;// CONCATENATED MODULE: external "jQuery"
var external_jQuery_namespaceObject = jQuery;
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_namespaceObject);
;// CONCATENATED MODULE: ./src/js/dark-mode/index.js
var _window, _window$wp;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }


var COLOR_SCHEME_BUTTON = '.is-color-scheme-switcher-button';
var STORAGE_ITEM = 'color-scheme-dark';
var TEMP_STORAGE_ITEM = 'color-scheme-dark-temp';
var $html = external_jQuery_default()('html');
var api = (_window = window) === null || _window === void 0 ? void 0 : (_window$wp = _window.wp) === null || _window$wp === void 0 ? void 0 : _window$wp.customize;
var ignoreStorage = !!api;

var DarkMode = /*#__PURE__*/function () {
  function DarkMode(element) {
    _classCallCheck(this, DarkMode);

    this.$element = external_jQuery_default()(element);
    this.$colorSchemeButtons = external_jQuery_default()(COLOR_SCHEME_BUTTON);
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

      external_jQuery_default()(document).on('click', COLOR_SCHEME_BUTTON, this.onClick.bind(this));
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
        var setting = api('sm_dark_mode_advanced');
        localStorage.removeItem(TEMP_STORAGE_ITEM);
        _this2.darkModeSetting = setting();

        _this2.update();

        setting.bind(function (newValue, oldValue) {
          localStorage.removeItem(TEMP_STORAGE_ITEM);
          _this2.darkModeSetting = newValue;

          _this2.update();
        });
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
      $html.toggleClass('is-dark', this.isCompiledDark());
    }
  }]);

  return DarkMode;
}();


var Dark = new DarkMode();
window.myApi = {};
window.myApi.isDark = Dark.isCompiledDark.bind(Dark);
/******/ })()
;