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
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ({

/***/ 0:
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),

/***/ 8:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DarkMode; });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(0);
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

/***/ })

/******/ });