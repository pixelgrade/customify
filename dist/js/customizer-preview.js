/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 946:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(3645);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".sm-overlay{position:fixed;top:0;left:0;z-index:9999;width:100%;height:100%;background:#fff;transition:all .2s ease;transition-property:opacity}.sm-overlay:not(.sm-overlay--visible){opacity:0;pointer-events:none}.sm-overlay,.sm-overlay *{font-family:system-ui}.palette-preview{display:grid;grid-template-columns:5em 1fr [content-start] minmax(auto, 800px) [content-end] 1fr 5em;margin:5em 0}.palette-preview>*{grid-column:content-start/content-end}.palette-preview>*+*{margin-top:1em}.palette-preview-header{display:flex;align-items:center;margin-left:-1em}.palette-preview-header>*{margin-left:1em}.palette-preview-set{--swatch-gap: 0.25em;position:relative;display:flex;margin-left:calc( -1 * var(--swatch-gap) );width:100%}.palette-preview-set>*{margin-left:var(--swatch-gap)}.palette-preview-swatches{display:flex;flex-direction:column;flex-grow:1}.palette-preview-swatches>:not(:first-child):before{padding-top:50%}.palette-preview-swatches>*{flex:1 1 0}.palette-preview-swatches>*:before{padding-top:100%;display:block;content:\"\";background:currentColor}.palette-preview-accent{--swatch-width: calc( 100% / 12 - var(--swatch-gap) );display:flex;position:absolute;top:0;left:0;width:calc( 100% * 14 / 24 );height:100%;padding:calc( 0.5 * var(--swatch-width) );padding-bottom:calc( 0.25 * var(--swatch-width) );clip-path:polygon(0 62.5%, 50% 62.5%, 50% 0, 100% 0, 100% 100%, 0 100%)}.palette-preview-accent:before{content:\"\";display:block;width:100%;border-bottom-left-radius:1em;border-bottom-right-radius:1em;border:2px dashed currentColor;border-top:0}.palette-preview-source{display:flex;flex-direction:row-reverse;justify-content:flex-start;margin-left:-5em;width:5em}.palette-preview-source>:not(:first-child){margin-right:-1em}.palette-preview-source-color{width:2em;flex:0 1 auto;background:currentColor;border-radius:50%}.palette-preview-source-color:before{content:\"\";display:block;padding-top:100%}", ""]);
// Exports
/* harmony default export */ __webpack_exports__["Z"] = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ 3645:
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

/***/ 1848:
/***/ (function(module) {

/**
 * The base implementation of `_.findIndex` and `_.findLastIndex` without
 * support for iteratee shorthands.
 *
 * @private
 * @param {Array} array The array to inspect.
 * @param {Function} predicate The function invoked per iteration.
 * @param {number} fromIndex The index to search from.
 * @param {boolean} [fromRight] Specify iterating from right to left.
 * @returns {number} Returns the index of the matched value, else `-1`.
 */
function baseFindIndex(array, predicate, fromIndex, fromRight) {
  var length = array.length,
      index = fromIndex + (fromRight ? 1 : -1);

  while ((fromRight ? index-- : ++index < length)) {
    if (predicate(array[index], index, array)) {
      return index;
    }
  }
  return -1;
}

module.exports = baseFindIndex;


/***/ }),

/***/ 4239:
/***/ (function(module) {

/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var nativeObjectToString = objectProto.toString;

/**
 * Converts `value` to a string using `Object.prototype.toString`.
 *
 * @private
 * @param {*} value The value to convert.
 * @returns {string} Returns the converted string.
 */
function objectToString(value) {
  return nativeObjectToString.call(value);
}

module.exports = objectToString;


/***/ }),

/***/ 2118:
/***/ (function(module) {

/**
 * A specialized version of `_.indexOf` which performs strict equality
 * comparisons of values, i.e. `===`.
 *
 * @private
 * @param {Array} array The array to inspect.
 * @param {*} value The value to search for.
 * @param {number} fromIndex The index to search from.
 * @returns {number} Returns the index of the matched value, else `-1`.
 */
function strictIndexOf(array, value, fromIndex) {
  var index = fromIndex - 1,
      length = array.length;

  while (++index < length) {
    if (array[index] === value) {
      return index;
    }
  }
  return -1;
}

module.exports = strictIndexOf;


/***/ }),

/***/ 7206:
/***/ (function(module) {

/**
 * This method returns the first argument it receives.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Util
 * @param {*} value Any value.
 * @returns {*} Returns `value`.
 * @example
 *
 * var object = { 'a': 1 };
 *
 * console.log(_.identity(object) === object);
 * // => true
 */
function identity(value) {
  return value;
}

module.exports = identity;


/***/ }),

/***/ 280:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var overArg = __webpack_require__(5569);

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeKeys = overArg(Object.keys, Object);

module.exports = nativeKeys;


/***/ }),

/***/ 7740:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseIteratee = __webpack_require__(7206),
    isArrayLike = __webpack_require__(8612),
    keys = __webpack_require__(3674);

/**
 * Creates a `_.find` or `_.findLast` function.
 *
 * @private
 * @param {Function} findIndexFunc The function to find the collection index.
 * @returns {Function} Returns the new find function.
 */
function createFind(findIndexFunc) {
  return function(collection, predicate, fromIndex) {
    var iterable = Object(collection);
    if (!isArrayLike(collection)) {
      var iteratee = baseIteratee(predicate, 3);
      collection = keys(collection);
      predicate = function(key) { return iteratee(iterable[key], key, iterable); };
    }
    var index = findIndexFunc(collection, predicate, fromIndex);
    return index > -1 ? iterable[iteratee ? collection[index] : index] : undefined;
  };
}

module.exports = createFind;


/***/ }),

/***/ 1957:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

/** Detect free variable `global` from Node.js. */
var freeGlobal = typeof __webpack_require__.g == 'object' && __webpack_require__.g && __webpack_require__.g.Object === Object && __webpack_require__.g;

module.exports = freeGlobal;


/***/ }),

/***/ 4160:
/***/ (function(module) {

/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var nativeObjectToString = objectProto.toString;

/**
 * Converts `value` to a string using `Object.prototype.toString`.
 *
 * @private
 * @param {*} value The value to convert.
 * @returns {string} Returns the converted string.
 */
function objectToString(value) {
  return nativeObjectToString.call(value);
}

module.exports = objectToString;


/***/ }),

/***/ 5726:
/***/ (function(module) {

/**
 * This method returns `false`.
 *
 * @static
 * @memberOf _
 * @since 4.13.0
 * @category Util
 * @returns {boolean} Returns `false`.
 * @example
 *
 * _.times(2, _.stubFalse);
 * // => [false, false]
 */
function stubFalse() {
  return false;
}

module.exports = stubFalse;


/***/ }),

/***/ 5569:
/***/ (function(module) {

/**
 * Creates a unary function that invokes `func` with its argument transformed.
 *
 * @private
 * @param {Function} func The function to wrap.
 * @param {Function} transform The argument transform.
 * @returns {Function} Returns the new function.
 */
function overArg(func, transform) {
  return function(arg) {
    return func(transform(arg));
  };
}

module.exports = overArg;


/***/ }),

/***/ 5639:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var freeGlobal = __webpack_require__(1957);

/** Detect free variable `self`. */
var freeSelf = typeof self == 'object' && self && self.Object === Object && self;

/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function('return this')();

module.exports = root;


/***/ }),

/***/ 3279:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var isObject = __webpack_require__(3218),
    now = __webpack_require__(7771),
    toNumber = __webpack_require__(4841);

/** Error message constants. */
var FUNC_ERROR_TEXT = 'Expected a function';

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeMax = Math.max,
    nativeMin = Math.min;

/**
 * Creates a debounced function that delays invoking `func` until after `wait`
 * milliseconds have elapsed since the last time the debounced function was
 * invoked. The debounced function comes with a `cancel` method to cancel
 * delayed `func` invocations and a `flush` method to immediately invoke them.
 * Provide `options` to indicate whether `func` should be invoked on the
 * leading and/or trailing edge of the `wait` timeout. The `func` is invoked
 * with the last arguments provided to the debounced function. Subsequent
 * calls to the debounced function return the result of the last `func`
 * invocation.
 *
 * **Note:** If `leading` and `trailing` options are `true`, `func` is
 * invoked on the trailing edge of the timeout only if the debounced function
 * is invoked more than once during the `wait` timeout.
 *
 * If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
 * until to the next tick, similar to `setTimeout` with a timeout of `0`.
 *
 * See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
 * for details over the differences between `_.debounce` and `_.throttle`.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Function
 * @param {Function} func The function to debounce.
 * @param {number} [wait=0] The number of milliseconds to delay.
 * @param {Object} [options={}] The options object.
 * @param {boolean} [options.leading=false]
 *  Specify invoking on the leading edge of the timeout.
 * @param {number} [options.maxWait]
 *  The maximum time `func` is allowed to be delayed before it's invoked.
 * @param {boolean} [options.trailing=true]
 *  Specify invoking on the trailing edge of the timeout.
 * @returns {Function} Returns the new debounced function.
 * @example
 *
 * // Avoid costly calculations while the window size is in flux.
 * jQuery(window).on('resize', _.debounce(calculateLayout, 150));
 *
 * // Invoke `sendMail` when clicked, debouncing subsequent calls.
 * jQuery(element).on('click', _.debounce(sendMail, 300, {
 *   'leading': true,
 *   'trailing': false
 * }));
 *
 * // Ensure `batchLog` is invoked once after 1 second of debounced calls.
 * var debounced = _.debounce(batchLog, 250, { 'maxWait': 1000 });
 * var source = new EventSource('/stream');
 * jQuery(source).on('message', debounced);
 *
 * // Cancel the trailing debounced invocation.
 * jQuery(window).on('popstate', debounced.cancel);
 */
function debounce(func, wait, options) {
  var lastArgs,
      lastThis,
      maxWait,
      result,
      timerId,
      lastCallTime,
      lastInvokeTime = 0,
      leading = false,
      maxing = false,
      trailing = true;

  if (typeof func != 'function') {
    throw new TypeError(FUNC_ERROR_TEXT);
  }
  wait = toNumber(wait) || 0;
  if (isObject(options)) {
    leading = !!options.leading;
    maxing = 'maxWait' in options;
    maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
    trailing = 'trailing' in options ? !!options.trailing : trailing;
  }

  function invokeFunc(time) {
    var args = lastArgs,
        thisArg = lastThis;

    lastArgs = lastThis = undefined;
    lastInvokeTime = time;
    result = func.apply(thisArg, args);
    return result;
  }

  function leadingEdge(time) {
    // Reset any `maxWait` timer.
    lastInvokeTime = time;
    // Start the timer for the trailing edge.
    timerId = setTimeout(timerExpired, wait);
    // Invoke the leading edge.
    return leading ? invokeFunc(time) : result;
  }

  function remainingWait(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime,
        timeWaiting = wait - timeSinceLastCall;

    return maxing
      ? nativeMin(timeWaiting, maxWait - timeSinceLastInvoke)
      : timeWaiting;
  }

  function shouldInvoke(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime;

    // Either this is the first call, activity has stopped and we're at the
    // trailing edge, the system time has gone backwards and we're treating
    // it as the trailing edge, or we've hit the `maxWait` limit.
    return (lastCallTime === undefined || (timeSinceLastCall >= wait) ||
      (timeSinceLastCall < 0) || (maxing && timeSinceLastInvoke >= maxWait));
  }

  function timerExpired() {
    var time = now();
    if (shouldInvoke(time)) {
      return trailingEdge(time);
    }
    // Restart the timer.
    timerId = setTimeout(timerExpired, remainingWait(time));
  }

  function trailingEdge(time) {
    timerId = undefined;

    // Only invoke if we have `lastArgs` which means `func` has been
    // debounced at least once.
    if (trailing && lastArgs) {
      return invokeFunc(time);
    }
    lastArgs = lastThis = undefined;
    return result;
  }

  function cancel() {
    if (timerId !== undefined) {
      clearTimeout(timerId);
    }
    lastInvokeTime = 0;
    lastArgs = lastCallTime = lastThis = timerId = undefined;
  }

  function flush() {
    return timerId === undefined ? result : trailingEdge(now());
  }

  function debounced() {
    var time = now(),
        isInvoking = shouldInvoke(time);

    lastArgs = arguments;
    lastThis = this;
    lastCallTime = time;

    if (isInvoking) {
      if (timerId === undefined) {
        return leadingEdge(lastCallTime);
      }
      if (maxing) {
        // Handle invocations in a tight loop.
        clearTimeout(timerId);
        timerId = setTimeout(timerExpired, wait);
        return invokeFunc(lastCallTime);
      }
    }
    if (timerId === undefined) {
      timerId = setTimeout(timerExpired, wait);
    }
    return result;
  }
  debounced.cancel = cancel;
  debounced.flush = flush;
  return debounced;
}

module.exports = debounce;


/***/ }),

/***/ 6073:
/***/ (function(module) {

/**
 * A specialized version of `_.forEach` for arrays without support for
 * iteratee shorthands.
 *
 * @private
 * @param {Array} [array] The array to iterate over.
 * @param {Function} iteratee The function invoked per iteration.
 * @returns {Array} Returns `array`.
 */
function arrayEach(array, iteratee) {
  var index = -1,
      length = array == null ? 0 : array.length;

  while (++index < length) {
    if (iteratee(array[index], index, array) === false) {
      break;
    }
  }
  return array;
}

module.exports = arrayEach;


/***/ }),

/***/ 3311:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var createFind = __webpack_require__(7740),
    findIndex = __webpack_require__(998);

/**
 * Iterates over elements of `collection`, returning the first element
 * `predicate` returns truthy for. The predicate is invoked with three
 * arguments: (value, index|key, collection).
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Collection
 * @param {Array|Object} collection The collection to inspect.
 * @param {Function} [predicate=_.identity] The function invoked per iteration.
 * @param {number} [fromIndex=0] The index to search from.
 * @returns {*} Returns the matched element, else `undefined`.
 * @example
 *
 * var users = [
 *   { 'user': 'barney',  'age': 36, 'active': true },
 *   { 'user': 'fred',    'age': 40, 'active': false },
 *   { 'user': 'pebbles', 'age': 1,  'active': true }
 * ];
 *
 * _.find(users, function(o) { return o.age < 40; });
 * // => object for 'barney'
 *
 * // The `_.matches` iteratee shorthand.
 * _.find(users, { 'age': 1, 'active': true });
 * // => object for 'pebbles'
 *
 * // The `_.matchesProperty` iteratee shorthand.
 * _.find(users, ['active', false]);
 * // => object for 'fred'
 *
 * // The `_.property` iteratee shorthand.
 * _.find(users, 'active');
 * // => object for 'barney'
 */
var find = createFind(findIndex);

module.exports = find;


/***/ }),

/***/ 998:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseFindIndex = __webpack_require__(1848),
    baseIteratee = __webpack_require__(7206),
    toInteger = __webpack_require__(554);

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeMax = Math.max;

/**
 * This method is like `_.find` except that it returns the index of the first
 * element `predicate` returns truthy for instead of the element itself.
 *
 * @static
 * @memberOf _
 * @since 1.1.0
 * @category Array
 * @param {Array} array The array to inspect.
 * @param {Function} [predicate=_.identity] The function invoked per iteration.
 * @param {number} [fromIndex=0] The index to search from.
 * @returns {number} Returns the index of the found element, else `-1`.
 * @example
 *
 * var users = [
 *   { 'user': 'barney',  'active': false },
 *   { 'user': 'fred',    'active': false },
 *   { 'user': 'pebbles', 'active': true }
 * ];
 *
 * _.findIndex(users, function(o) { return o.user == 'barney'; });
 * // => 0
 *
 * // The `_.matches` iteratee shorthand.
 * _.findIndex(users, { 'user': 'fred', 'active': false });
 * // => 1
 *
 * // The `_.matchesProperty` iteratee shorthand.
 * _.findIndex(users, ['active', false]);
 * // => 0
 *
 * // The `_.property` iteratee shorthand.
 * _.findIndex(users, 'active');
 * // => 2
 */
function findIndex(array, predicate, fromIndex) {
  var length = array == null ? 0 : array.length;
  if (!length) {
    return -1;
  }
  var index = fromIndex == null ? 0 : toInteger(fromIndex);
  if (index < 0) {
    index = nativeMax(length + index, 0);
  }
  return baseFindIndex(array, baseIteratee(predicate, 3), index);
}

module.exports = findIndex;


/***/ }),

/***/ 8721:
/***/ (function(module) {

/** Used for built-in method references. */
var objectProto = Object.prototype;

/** Used to check objects for own properties. */
var hasOwnProperty = objectProto.hasOwnProperty;

/**
 * The base implementation of `_.has` without support for deep paths.
 *
 * @private
 * @param {Object} [object] The object to query.
 * @param {Array|string} key The key to check.
 * @returns {boolean} Returns `true` if `key` exists, else `false`.
 */
function baseHas(object, key) {
  return object != null && hasOwnProperty.call(object, key);
}

module.exports = baseHas;


/***/ }),

/***/ 4721:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseIndexOf = __webpack_require__(2118);

/**
 * A specialized version of `_.includes` for arrays without support for
 * specifying an index to search from.
 *
 * @private
 * @param {Array} [array] The array to inspect.
 * @param {*} target The value to search for.
 * @returns {boolean} Returns `true` if `target` is found, else `false`.
 */
function arrayIncludes(array, value) {
  var length = array == null ? 0 : array.length;
  return !!length && baseIndexOf(array, value, 0) > -1;
}

module.exports = arrayIncludes;


/***/ }),

/***/ 5694:
/***/ (function(module) {

/**
 * This method returns `false`.
 *
 * @static
 * @memberOf _
 * @since 4.13.0
 * @category Util
 * @returns {boolean} Returns `false`.
 * @example
 *
 * _.times(2, _.stubFalse);
 * // => [false, false]
 */
function stubFalse() {
  return false;
}

module.exports = stubFalse;


/***/ }),

/***/ 1469:
/***/ (function(module) {

/**
 * Checks if `value` is classified as an `Array` object.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is an array, else `false`.
 * @example
 *
 * _.isArray([1, 2, 3]);
 * // => true
 *
 * _.isArray(document.body.children);
 * // => false
 *
 * _.isArray('abc');
 * // => false
 *
 * _.isArray(_.noop);
 * // => false
 */
var isArray = Array.isArray;

module.exports = isArray;


/***/ }),

/***/ 8612:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var isFunction = __webpack_require__(3560),
    isLength = __webpack_require__(1780);

/**
 * Checks if `value` is array-like. A value is considered array-like if it's
 * not a function and has a `value.length` that's an integer greater than or
 * equal to `0` and less than or equal to `Number.MAX_SAFE_INTEGER`.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is array-like, else `false`.
 * @example
 *
 * _.isArrayLike([1, 2, 3]);
 * // => true
 *
 * _.isArrayLike(document.body.children);
 * // => true
 *
 * _.isArrayLike('abc');
 * // => true
 *
 * _.isArrayLike(_.noop);
 * // => false
 */
function isArrayLike(value) {
  return value != null && isLength(value.length) && !isFunction(value);
}

module.exports = isArrayLike;


/***/ }),

/***/ 4144:
/***/ (function(module) {

/**
 * This method returns `false`.
 *
 * @static
 * @memberOf _
 * @since 4.13.0
 * @category Util
 * @returns {boolean} Returns `false`.
 * @example
 *
 * _.times(2, _.stubFalse);
 * // => [false, false]
 */
function stubFalse() {
  return false;
}

module.exports = stubFalse;


/***/ }),

/***/ 1609:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseKeys = __webpack_require__(280),
    getTag = __webpack_require__(4160),
    isArguments = __webpack_require__(5694),
    isArray = __webpack_require__(1469),
    isArrayLike = __webpack_require__(8612),
    isBuffer = __webpack_require__(4144),
    isPrototype = __webpack_require__(5726),
    isTypedArray = __webpack_require__(6719);

/** `Object#toString` result references. */
var mapTag = '[object Map]',
    setTag = '[object Set]';

/** Used for built-in method references. */
var objectProto = Object.prototype;

/** Used to check objects for own properties. */
var hasOwnProperty = objectProto.hasOwnProperty;

/**
 * Checks if `value` is an empty object, collection, map, or set.
 *
 * Objects are considered empty if they have no own enumerable string keyed
 * properties.
 *
 * Array-like values such as `arguments` objects, arrays, buffers, strings, or
 * jQuery-like collections are considered empty if they have a `length` of `0`.
 * Similarly, maps and sets are considered empty if they have a `size` of `0`.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is empty, else `false`.
 * @example
 *
 * _.isEmpty(null);
 * // => true
 *
 * _.isEmpty(true);
 * // => true
 *
 * _.isEmpty(1);
 * // => true
 *
 * _.isEmpty([1, 2, 3]);
 * // => false
 *
 * _.isEmpty({ 'a': 1 });
 * // => false
 */
function isEmpty(value) {
  if (value == null) {
    return true;
  }
  if (isArrayLike(value) &&
      (isArray(value) || typeof value == 'string' || typeof value.splice == 'function' ||
        isBuffer(value) || isTypedArray(value) || isArguments(value))) {
    return !value.length;
  }
  var tag = getTag(value);
  if (tag == mapTag || tag == setTag) {
    return !value.size;
  }
  if (isPrototype(value)) {
    return !baseKeys(value).length;
  }
  for (var key in value) {
    if (hasOwnProperty.call(value, key)) {
      return false;
    }
  }
  return true;
}

module.exports = isEmpty;


/***/ }),

/***/ 3560:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseGetTag = __webpack_require__(4239),
    isObject = __webpack_require__(3218);

/** `Object#toString` result references. */
var asyncTag = '[object AsyncFunction]',
    funcTag = '[object Function]',
    genTag = '[object GeneratorFunction]',
    proxyTag = '[object Proxy]';

/**
 * Checks if `value` is classified as a `Function` object.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a function, else `false`.
 * @example
 *
 * _.isFunction(_);
 * // => true
 *
 * _.isFunction(/abc/);
 * // => false
 */
function isFunction(value) {
  if (!isObject(value)) {
    return false;
  }
  // The use of `Object#toString` avoids issues with the `typeof` operator
  // in Safari 9 which returns 'object' for typed arrays and other constructors.
  var tag = baseGetTag(value);
  return tag == funcTag || tag == genTag || tag == asyncTag || tag == proxyTag;
}

module.exports = isFunction;


/***/ }),

/***/ 1780:
/***/ (function(module) {

/** Used as references for various `Number` constants. */
var MAX_SAFE_INTEGER = 9007199254740991;

/**
 * Checks if `value` is a valid array-like length.
 *
 * **Note:** This method is loosely based on
 * [`ToLength`](http://ecma-international.org/ecma-262/7.0/#sec-tolength).
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a valid length, else `false`.
 * @example
 *
 * _.isLength(3);
 * // => true
 *
 * _.isLength(Number.MIN_VALUE);
 * // => false
 *
 * _.isLength(Infinity);
 * // => false
 *
 * _.isLength('3');
 * // => false
 */
function isLength(value) {
  return typeof value == 'number' &&
    value > -1 && value % 1 == 0 && value <= MAX_SAFE_INTEGER;
}

module.exports = isLength;


/***/ }),

/***/ 1763:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseGetTag = __webpack_require__(4239),
    isObjectLike = __webpack_require__(7005);

/** `Object#toString` result references. */
var numberTag = '[object Number]';

/**
 * Checks if `value` is classified as a `Number` primitive or object.
 *
 * **Note:** To exclude `Infinity`, `-Infinity`, and `NaN`, which are
 * classified as numbers, use the `_.isFinite` method.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a number, else `false`.
 * @example
 *
 * _.isNumber(3);
 * // => true
 *
 * _.isNumber(Number.MIN_VALUE);
 * // => true
 *
 * _.isNumber(Infinity);
 * // => true
 *
 * _.isNumber('3');
 * // => false
 */
function isNumber(value) {
  return typeof value == 'number' ||
    (isObjectLike(value) && baseGetTag(value) == numberTag);
}

module.exports = isNumber;


/***/ }),

/***/ 3218:
/***/ (function(module) {

/**
 * Checks if `value` is the
 * [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)
 * of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is an object, else `false`.
 * @example
 *
 * _.isObject({});
 * // => true
 *
 * _.isObject([1, 2, 3]);
 * // => true
 *
 * _.isObject(_.noop);
 * // => true
 *
 * _.isObject(null);
 * // => false
 */
function isObject(value) {
  var type = typeof value;
  return value != null && (type == 'object' || type == 'function');
}

module.exports = isObject;


/***/ }),

/***/ 7005:
/***/ (function(module) {

/**
 * Checks if `value` is object-like. A value is object-like if it's not `null`
 * and has a `typeof` result of "object".
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
 * @example
 *
 * _.isObjectLike({});
 * // => true
 *
 * _.isObjectLike([1, 2, 3]);
 * // => true
 *
 * _.isObjectLike(_.noop);
 * // => false
 *
 * _.isObjectLike(null);
 * // => false
 */
function isObjectLike(value) {
  return value != null && typeof value == 'object';
}

module.exports = isObjectLike;


/***/ }),

/***/ 7037:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var baseGetTag = __webpack_require__(4239),
    isArray = __webpack_require__(1469),
    isObjectLike = __webpack_require__(7005);

/** `Object#toString` result references. */
var stringTag = '[object String]';

/**
 * Checks if `value` is classified as a `String` primitive or object.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a string, else `false`.
 * @example
 *
 * _.isString('abc');
 * // => true
 *
 * _.isString(1);
 * // => false
 */
function isString(value) {
  return typeof value == 'string' ||
    (!isArray(value) && isObjectLike(value) && baseGetTag(value) == stringTag);
}

module.exports = isString;


/***/ }),

/***/ 6719:
/***/ (function(module) {

/**
 * This method returns `false`.
 *
 * @static
 * @memberOf _
 * @since 4.13.0
 * @category Util
 * @returns {boolean} Returns `false`.
 * @example
 *
 * _.times(2, _.stubFalse);
 * // => [false, false]
 */
function stubFalse() {
  return false;
}

module.exports = stubFalse;


/***/ }),

/***/ 3674:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var overArg = __webpack_require__(5569);

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeKeys = overArg(Object.keys, Object);

module.exports = nativeKeys;


/***/ }),

/***/ 7771:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var root = __webpack_require__(5639);

/**
 * Gets the timestamp of the number of milliseconds that have elapsed since
 * the Unix epoch (1 January 1970 00:00:00 UTC).
 *
 * @static
 * @memberOf _
 * @since 2.4.0
 * @category Date
 * @returns {number} Returns the timestamp.
 * @example
 *
 * _.defer(function(stamp) {
 *   console.log(_.now() - stamp);
 * }, _.now());
 * // => Logs the number of milliseconds it took for the deferred invocation.
 */
var now = function() {
  return root.Date.now();
};

module.exports = now;


/***/ }),

/***/ 554:
/***/ (function(module) {

/**
 * This method returns the first argument it receives.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Util
 * @param {*} value Any value.
 * @returns {*} Returns `value`.
 * @example
 *
 * var object = { 'a': 1 };
 *
 * console.log(_.identity(object) === object);
 * // => true
 */
function identity(value) {
  return value;
}

module.exports = identity;


/***/ }),

/***/ 4841:
/***/ (function(module) {

/**
 * This method returns the first argument it receives.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Util
 * @param {*} value Any value.
 * @returns {*} Returns `value`.
 * @example
 *
 * var object = { 'a': 1 };
 *
 * console.log(_.identity(object) === object);
 * // => true
 */
function identity(value) {
  return value;
}

module.exports = identity;


/***/ }),

/***/ 3379:
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
/******/ 	/* webpack/runtime/global */
/******/ 	!function() {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
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

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": function() { return /* binding */ CustomizerPreview; }
});

// EXTERNAL MODULE: ./node_modules/lodash/debounce.js
var debounce = __webpack_require__(3279);
var debounce_default = /*#__PURE__*/__webpack_require__.n(debounce);
// EXTERNAL MODULE: ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js
var injectStylesIntoStyleTag = __webpack_require__(3379);
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
var external_jQuery_ = __webpack_require__(3609);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);
// EXTERNAL MODULE: ./node_modules/lodash/find.js
var find = __webpack_require__(3311);
var find_default = /*#__PURE__*/__webpack_require__.n(find);
// EXTERNAL MODULE: ./node_modules/lodash/has.js
var has = __webpack_require__(8721);
var has_default = /*#__PURE__*/__webpack_require__.n(has);
// EXTERNAL MODULE: ./node_modules/lodash/isEmpty.js
var isEmpty = __webpack_require__(1609);
var isEmpty_default = /*#__PURE__*/__webpack_require__.n(isEmpty);
// EXTERNAL MODULE: ./node_modules/lodash/each.js
var each = __webpack_require__(6073);
var each_default = /*#__PURE__*/__webpack_require__.n(each);
// EXTERNAL MODULE: ./node_modules/lodash/isNumber.js
var isNumber = __webpack_require__(1763);
var isNumber_default = /*#__PURE__*/__webpack_require__.n(isNumber);
// EXTERNAL MODULE: ./node_modules/lodash/isString.js
var isString = __webpack_require__(7037);
var isString_default = /*#__PURE__*/__webpack_require__.n(isString);
// EXTERNAL MODULE: ./node_modules/lodash/includes.js
var includes = __webpack_require__(4721);
var includes_default = /*#__PURE__*/__webpack_require__.n(includes);
;// CONCATENATED MODULE: ./src/_js/customizer-preview/utils.js








function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }


// Mirror logic of server-side Utils\Fonts::getCSSValue()
var getFontFieldCSSValue = function getFontFieldCSSValue(settingID, value) {
  var CSSValue = {};

  if (typeof value.font_family !== 'undefined' && !includes_default()(['', 'false', false], value.font_family)) {
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

  if (typeof value.font_variant !== 'undefined' && !includes_default()(['', 'false', false], value.font_variant)) {
    var variant = value.font_variant;

    if (isString_default()(variant)) {
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
    } else if (isNumber_default()(variant)) {
      CSSValue['font-weight'] = String(variant);
    }
  }

  if (typeof value.font_size !== 'undefined' && !includes_default()(['', 'false', false], value.font_size)) {
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

  if (typeof value.letter_spacing !== 'undefined' && !includes_default()(['', 'false', false], value.letter_spacing)) {
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

  if (typeof value.line_height !== 'undefined' && !includes_default()(['', 'false', false], value.line_height)) {
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

  if (typeof value.text_align !== 'undefined' && !includes_default()(['', 'false', false], value.text_align)) {
    CSSValue['text-align'] = value.text_align;
  }

  if (typeof value.text_transform !== 'undefined' && !includes_default()(['', 'false', false], value.text_transform)) {
    CSSValue['text-transform'] = value.text_transform;
  }

  if (typeof value.text_decoration !== 'undefined' && !includes_default()(['', 'false', false], value.text_decoration)) {
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

    each_default()(fontConfig.selector, function (details, selector) {
      plainSelectors.push(selector);
    });

    var adjustedFontConfig = external_jQuery_default().extend(true, {}, fontConfig);
    adjustedFontConfig.selector = plainSelectors.join(', '); // Also, "kill" all fields unit since we pass final CSS values.
    // @todo For some reason, the client-side Typeline cbs are not consistent and expect the font-size value with unit.

    each_default()(adjustedFontConfig['fields'], function (fieldValue, fieldKey) {
      if (typeof fieldValue.unit !== 'undefined') {
        adjustedFontConfig['fields'][fieldKey]['unit'] = false;
      }
    }); // Callbacks want the value keys with underscores, not dashes.
    // We will provide them in both versions for a smoother transition.


    each_default()(cssValue, function (propertyValue, property) {
      var newKey = property.replace(regexForMultipleReplace, '_');
      cssValue[newKey] = propertyValue;
    });

    return window[fontConfig.callback](cssValue, adjustedFontConfig);
  }

  if (typeof fontConfig.selector === 'undefined' || isEmpty_default()(fontConfig.selector) || isEmpty_default()(cssValue)) {
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

  each_default()(fontConfig.selector, function (details, selector) {
    if (isEmpty_default()(details.properties)) {
      // This is a simple selector.
      simpleCSSSelectors.push(selector);
    } else {
      complexCSSSelectors[selector] = details;
    }
  });

  if (!isEmpty_default()(simpleCSSSelectors)) {
    output += '\n' + simpleCSSSelectors.join(', ') + ' {\n';
    output += getFontFieldCSSProperties(cssValue, subFieldsCSSAllowedProperties, prefix);
    output += '}\n';
  }

  if (!isEmpty_default()(complexCSSSelectors)) {
    each_default()(complexCSSSelectors, function (details, selector) {
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
    if (includes_default()(['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'], field)) {
      return false;
    } // The rest of the subfields have pixels as default units.


    return 'px';
  }

  if (typeof customify.config.settings[settingID].fields[field].unit !== 'undefined') {
    // Make sure that we convert all falsy unit values to the boolean false.
    return includes_default()(['', 'false', false], customify.config.settings[settingID].fields[field].unit) ? false : customify.config.settings[settingID].fields[field].unit;
  }

  if (typeof customify.config.settings[settingID].fields[field][3] !== 'undefined') {
    // Make sure that we convert all falsy unit values to the boolean false.
    return includes_default()(['', 'false', false], customify.config.settings[settingID].fields[field][3]) ? false : customify.config.settings[settingID].fields[field][3];
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
  if (isEmpty_default()(property)) {
    return false;
  } // Everything is allowed if nothing is specified.


  if (isEmpty_default()(allowedProperties)) {
    return true;
  } // For arrays


  if (includes_default()(allowedProperties, property)) {
    return true;
  } // For objects


  if (has_default()(allowedProperties, property) && allowedProperties[property]) {
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

  if (isEmpty_default()(subfields)) {
    return allowedProperties;
  } // We will match the subfield keys with the CSS properties, but only those that properties that are allowed.
  // Maybe at some point some more complex matching would be needed here.


  each_default()(subfields, function (value, key) {
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
    && includes_default()(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
    ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : [];

    if (!isEmpty_default()(variants)) {
      variants = standardizeToArray(variants);

      if (!isEmpty_default()(variants)) {
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
      && includes_default()(fontDetails.variants, font.font_variant) // If the value variant is not amongst the available ones, load all available variants.
      ? font.font_variant : typeof fontDetails.variants !== 'undefined' ? fontDetails.variants : [];

      if (!isEmpty_default()(_variants)) {
        _variants = standardizeToArray(_variants);

        if (!isEmpty_default()(_variants)) {
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

  if (typeof fontDetails.fallback_stack !== 'undefined' && !isEmpty_default()(fontDetails.fallback_stack)) {
    fallbackStack = fontDetails.fallback_stack;
  } else if (typeof fontDetails.category !== 'undefined' && !isEmpty_default()(fontDetails.category)) {
    var category = fontDetails.category; // Search in the available categories for a match.

    if (typeof customify.fonts.categories[category] !== 'undefined') {
      // Matched by category ID/key
      fallbackStack = typeof customify.fonts.categories[category].fallback_stack !== 'undefined' ? customify.fonts.categories[category].fallback_stack : '';
    } else {
      // We need to search for aliases.
      find_default()(customify.fonts.categories, function (categoryDetails) {
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

  each_default()(fontFamilies, function (fontFamily, key) {
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


var _window, _parent, _window2, _parent2;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }




window.fontsCache = [];
window.wp = ((_window = window) === null || _window === void 0 ? void 0 : _window.wp) || ((_parent = parent) === null || _parent === void 0 ? void 0 : _parent.wp);
window.customify = ((_window2 = window) === null || _window2 === void 0 ? void 0 : _window2.customify) || ((_parent2 = parent) === null || _parent2 === void 0 ? void 0 : _parent2.customify);

var CustomizerPreview = /*#__PURE__*/function () {
  function CustomizerPreview() {
    _classCallCheck(this, CustomizerPreview);

    this.initialize();
  }

  _createClass(CustomizerPreview, [{
    key: "initialize",
    value: function initialize() {
      this.bindEvents();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      external_jQuery_default()(window).on('load', this.onLoad.bind(this));
      external_jQuery_default()(document).on('ready', this.onDocReady.bind(this));
    }
  }, {
    key: "onLoad",
    value: function onLoad() {
      // We need to do this on window.load because on document.ready might be too early.
      this.maybeLoadWebfontloaderScript();
    }
  }, {
    key: "onDocReady",
    value: function onDocReady() {
      var _this = this;

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

      var onChange = debounce_default()(function () {
        var queue = Object.assign({}, updateQueue);
        updateQueue = {};
        Object.keys(queue).forEach(function (settingID) {
          var idAttr = getStyleTagID(settingID);
          var style = document.getElementById(idAttr);
          var newValue = queue[settingID];
          var settingConfig = settings[settingID];
          style.innerHTML = _this.getSettingCSS(settingID, newValue, settingConfig);
        });
      }, 100);

      properKeys.forEach(function (settingID) {
        window.wp.customize(settingID, function (setting) {
          setting.bind(function (newValue) {
            updateQueue[settingID] = newValue;
            onChange();
          });
        });
      });
    }
  }, {
    key: "maybeLoadWebfontloaderScript",
    value: function maybeLoadWebfontloaderScript() {
      if (typeof WebFont === 'undefined') {
        var tk = document.createElement('script');
        tk.src = parent.customify.config.webfontloader_url;
        tk.type = 'text/javascript';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(tk, s);
      }
    }
  }, {
    key: "defaultCallbackFilter",
    value: function defaultCallbackFilter(value, selector, property) {
      var unit = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
      return "".concat(selector, " { ").concat(property, ": ").concat(value).concat(unit, "; }");
    }
  }, {
    key: "getSettingCSS",
    value: function getSettingCSS(settingID, newValue, settingConfig) {
      var _this2 = this;

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
        var settingCallback = callback_filter && typeof window[callback_filter] === 'function' ? window[callback_filter] : _this2.defaultCallbackFilter;

        if (!selector || !property) {
          return acc;
        }

        return "".concat(acc, "\n      ").concat(settingCallback(newValue, selector, property, unit));
      }, '');
    }
  }]);

  return CustomizerPreview;
}();


var Previewer = new CustomizerPreview();
}();
(this.sm = this.sm || {}).customizerPreview = __webpack_exports__;
/******/ })()
;