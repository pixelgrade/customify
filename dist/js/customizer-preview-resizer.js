/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
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
var __webpack_exports__ = {};
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "setOffset": function() { return /* binding */ setOffset; },
/* harmony export */   "resize": function() { return /* binding */ resize; }
/* harmony export */ });
var defaultOffset = {
  top: 0,
  right: 0,
  bottom: 0,
  left: 0
};
var offset = defaultOffset;
wp.customize.bind('ready', function () {
  setOffset({
    top: 10,
    right: 10,
    bottom: 10,
    left: 10
  });
  resize();
  window.addEventListener('resize', resize);
  wp.customize.previewedDevice.bind(resize);
  var collapseSidebar = document.querySelector('.collapse-sidebar');

  if (!collapseSidebar) {
    return;
  }

  collapseSidebar.addEventListener('click', function () {
    setTimeout(resize, 300);
  });
});
var setOffset = function setOffset(newOffset) {
  offset = Object.assign({}, newOffset);
};
var resize = function resize() {
  var preview = document.querySelector('.wp-full-overlay');
  var iframe = document.querySelector('#customize-preview iframe');
  var previewedDevice = wp.customize.previewedDevice.get();

  if (!iframe || !preview) {
    return;
  } // remove CSS properties that may have been previously added


  iframe.style.removeProperty('width');
  iframe.style.removeProperty('height');
  iframe.style.removeProperty('transform-origin');
  iframe.style.removeProperty('transform');
  iframe.style.removeProperty('margin-top');
  iframe.style.removeProperty('margin-left');

  if (!iframe) {
    return;
  } // scaling of the site preview should be done only in desktop preview mode


  if (previewedDevice !== 'desktop') {
    return;
  }

  var windowWidth = window.innerWidth;
  var windowHeight = window.innerHeight;
  var previewWidth = preview.offsetWidth;
  var previewHeight = preview.offsetHeight; // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the preview and use CSS transforms to scale it back down

  var shouldScale = previewWidth > 720 && previewWidth < 1100;
  var initialHeight = previewHeight;
  var finalHeight = previewHeight - offset.top - offset.bottom;
  var initialWidth = shouldScale ? windowWidth : previewWidth;
  var finalWidth = previewWidth - offset.left - offset.right;
  var scaleX = initialWidth / finalWidth;
  var scaleY = initialHeight / finalHeight;
  var scale = Math.max(scaleX, scaleY);
  iframe.style.width = "".concat(finalWidth * scale, "px");
  iframe.style.height = "".concat(finalHeight * scale, "px");
  iframe.style.transformOrigin = "left top";
  iframe.style.transform = "scale( ".concat(1 / scale, " )");
  iframe.style.marginTop = "".concat(offset.top, "px");
  iframe.style.marginLeft = "".concat(offset.left, "px");
};
(window.sm = window.sm || {}).customizerPreviewResizer = __webpack_exports__;
/******/ })()
;