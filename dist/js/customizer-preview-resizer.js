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

/***/ "./src/_js/customizer-preview-resizer/index.js":
/*!*****************************************************!*\
  !*** ./src/_js/customizer-preview-resizer/index.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"setOffset\": function() { return /* binding */ setOffset; },\n/* harmony export */   \"resize\": function() { return /* binding */ resize; }\n/* harmony export */ });\nvar offset = {\n  top: 0,\n  right: 0,\n  bottom: 0,\n  left: 0\n};\nwp.customize.bind('ready', function () {\n  setOffset({\n    top: 10,\n    right: 10,\n    bottom: 10,\n    left: 10\n  });\n  resize();\n  window.addEventListener('resize', resize);\n  wp.customize.previewedDevice.bind(resize);\n  var collapseSidebar = document.querySelector('.collapse-sidebar');\n\n  if (!collapseSidebar) {\n    return;\n  }\n\n  collapseSidebar.addEventListener('click', function () {\n    setTimeout(resize, 300);\n  });\n});\nvar setOffset = function setOffset(newOffset) {\n  offset = Object.assign({}, newOffset);\n};\nvar resize = function resize() {\n  var iframe = document.querySelector('#customize-preview iframe');\n\n  if (!iframe) {\n    return;\n  } // remove CSS properties that may have been previously added\n\n\n  iframe.style.removeProperty('width');\n  iframe.style.removeProperty('height');\n  iframe.style.removeProperty('transformOrigin');\n  iframe.style.removeProperty('transform');\n  iframe.style.removeProperty('marginTop');\n  iframe.style.removeProperty('marginLeft'); // scaling of the site preview should be done only in desktop preview mode\n\n  if (wp.customize.previewedDevice.get() !== 'desktop') {\n    return;\n  }\n\n  var windowWidth = window.innerWidth;\n  var windowHeight = window.innerHeight;\n  var iframeWidth = iframe.offsetWidth - offset.left - offset.right;\n  var iframeHeight = windowHeight - offset.top - offset.bottom; // get the ratio between the site preview and actual browser width\n\n  var scale = windowWidth / iframeWidth; // for an accurate preview at resolutions where media queries may intervene\n  // increase the width of the iframe and use CSS transforms to scale it back down\n\n  if (iframeWidth > 720 && iframeWidth < 1100) {\n    iframe.style.width = \"\".concat(iframeWidth * scale, \"px\");\n    iframe.style.height = \"\".concat(iframeHeight * scale, \"px\");\n    iframe.style.transformOrigin = \"left top\";\n    iframe.style.transform = \"scale( \".concat(1 / scale, \" )\");\n    iframe.style.marginTop = \"\".concat(offset.top, \"px\");\n    iframe.style.marginLeft = \"\".concat(offset.left, \"px\");\n  }\n};\n\n//# sourceURL=webpack://sm.%5Bname%5D/./src/_js/customizer-preview-resizer/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
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
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/_js/customizer-preview-resizer/index.js"](0, __webpack_exports__, __webpack_require__);
/******/ 	(window.sm = window.sm || {}).customizerPreviewResizer = __webpack_exports__;
/******/ 	
/******/ })()
;