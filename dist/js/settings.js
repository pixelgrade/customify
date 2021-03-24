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

/***/ "./src/js/settings/index.js":
/*!**********************************!*\
  !*** ./src/js/settings/index.js ***!
  \**********************************/
/***/ (function() {

eval("(function ($) {\n  'use strict';\n\n  $(function () {\n    $(document).ready(function () {\n      $('#reset_theme_mods').on('click', function () {\n        var confirm = window.confirm('Are you sure?');\n\n        if (!confirm) {\n          return false;\n        }\n\n        $.ajax({\n          url: customify.config.wp_rest.root + 'customify/v1/delete_theme_mod',\n          method: 'POST',\n          beforeSend: function beforeSend(xhr) {\n            xhr.setRequestHeader('X-WP-Nonce', customify.config.wp_rest.nonce);\n          },\n          data: {\n            'customify_settings_nonce': customify.config.wp_rest.customify_settings_nonce\n          }\n        }).done(function (response) {\n          if (response.success) {\n            alert('Success: ' + response.data);\n          } else {\n            alert('No luck: ' + response.data);\n          }\n        }).error(function (e) {\n          console.log(e);\n        });\n      });\n      /* Ensure groups visibility */\n\n      $('.switch input[type=checkbox], .select select').each(function () {\n        if ($(this).data('show_group')) {\n          var show = false;\n\n          if ($(this).attr('checked')) {\n            show = true;\n          } else if (typeof $(this).data('display_option') !== 'undefined' && $(this).data('display_option') === $(this).val()) {\n            show = true;\n          }\n\n          toggleGroup($(this).data('show_group'), show);\n        }\n      });\n      $('.switch, .select ').on('change', 'input[type=checkbox], select', function () {\n        if ($(this).data('show_group')) {\n          var show = false;\n\n          if ($(this).attr('checked')) {\n            show = true;\n          } else if (typeof $(this).data('display_option') !== 'undefined' && $(this).data('display_option') === $(this).val()) {\n            show = true;\n          }\n\n          toggleGroup($(this).data('show_group'), show);\n        }\n      });\n    });\n\n    var toggleGroup = function toggleGroup(name, show) {\n      var $group = $('#' + name);\n\n      if (show) {\n        $group.show();\n      } else {\n        $group.hide();\n      }\n    };\n  });\n\n  $.fn.check_for_extended_options = function () {\n    var extended_options = $(this).siblings('fieldset.group');\n\n    if ($(this).data('show-next')) {\n      if (extended_options.data('extended') === true) {\n        extended_options.data('extended', false).css('height', '0');\n      } else if (typeof extended_options.data('extended') === 'undefined' && $(this).attr('checked') === 'checked' || extended_options.data('extended') === false) {\n        extended_options.data('extended', true).css('height', 'auto');\n      }\n    }\n  };\n})(jQuery);\n\n//# sourceURL=webpack://sm.%5Bname%5D/./src/js/settings/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/js/settings/index.js"]();
/******/ 	(this.sm = this.sm || {}).settings = __webpack_exports__;
/******/ 	
/******/ })()
;