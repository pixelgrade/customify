/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/_js/settings/index.js":
/*!***********************************!*\
  !*** ./src/_js/settings/index.js ***!
  \***********************************/
/***/ (function() {

eval("(function ($) {\n  'use strict';\n\n  $(document).ready(function () {\n    $('#reset_customizer_settings').on('click', function () {\n      var confirm = window.confirm('Are you sure you want to do this?');\n\n      if (!confirm) {\n        return false;\n      }\n\n      $.ajax({\n        url: customify.config.wp_rest.root + 'customify/v1/delete_customizer_settings',\n        method: 'POST',\n        beforeSend: function beforeSend(xhr) {\n          xhr.setRequestHeader('X-WP-Nonce', customify.config.wp_rest.nonce);\n        },\n        data: {\n          'customify_settings_nonce': customify.config.wp_rest.customify_settings_nonce\n        }\n      }).done(function (response) {\n        if (response.success) {\n          alert('Success: ' + response.data);\n        } else {\n          alert('Unfortunately, no luck: ' + response.data);\n        }\n      }).error(function (e) {\n        console.log(e);\n      });\n    });\n  });\n})(jQuery);\n\n//# sourceURL=webpack://sm.%5Bname%5D/./src/_js/settings/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/_js/settings/index.js"]();
/******/ 	(this.sm = this.sm || {}).settings = __webpack_exports__;
/******/ 	
/******/ })()
;