/**
 * regression-js v2.0.1
 * https://github.com/Tom-Alexander/regression-js
 *
 * @license MIT
 * https://github.com/Tom-Alexander/regression-js/blob/master/LICENSE
 */
(function (global, factory) {
	if (typeof define === "function" && define.amd) {
		define(['module'], factory);
	} else if (typeof exports !== "undefined") {
		factory(module);
	} else {
    const mod = {
      exports: {}
    }
    factory(mod);
		global.regression = mod.exports;
	}
})(this, function (module) {
	'use strict';

	function _defineProperty(obj, key, value) {
		if (key in obj) {
			Object.defineProperty(obj, key, {
				value: value,
				enumerable: true,
				configurable: true,
				writable: true
			});
		} else {
			obj[key] = value;
		}

		return obj;
	}

  const _extends = Object.assign || function (target) {
    for (let i = 1; i < arguments.length; i++) {
      const source = arguments[i]

      for (let key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key]
        }
      }
    }

    return target
  }

  function _toConsumableArray(arr) {
		if (Array.isArray(arr)) {
			let arr2 = Array(arr.length)

      for (let i = 0; i < arr.length; i++) {
				arr2[i] = arr[i];
			}

			return arr2;
		} else {
			return Array.from(arr);
		}
	}

  const DEFAULT_OPTIONS = {order: 2, precision: 2, period: null}

  /**
	 * Determine the coefficient of determination (r^2) of a fit from the observations
	 * and predictions.
	 *
	 * @param {Array<Array<number>>} data - Pairs of observed x-y values
	 * @param {Array<Array<number>>} results - Pairs of observed predicted x-y values
	 *
	 * @return {number} - The r^2 value, or NaN if one cannot be calculated.
	 */
	function determinationCoefficient(data, results) {
    const predictions = []
    const observations = []

    data.forEach(function (d, i) {
			if (d[1] !== null) {
				observations.push(d);
				predictions.push(results[i]);
			}
		});

    const sum = observations.reduce(function (a, observation) {
      return a + observation[1]
    }, 0)
    const mean = sum / observations.length

    const ssyy = observations.reduce(function (a, observation) {
      const difference = observation[1] - mean
      return a + difference * difference
    }, 0)

    const sse = observations.reduce(function (accum, observation, index) {
      const prediction = predictions[index]
      const residual = observation[1] - prediction[1]
      return accum + residual * residual
    }, 0)

    return 1 - sse / ssyy;
	}

	/**
	 * Determine the solution of a system of linear equations A * x = b using
	 * Gaussian elimination.
	 *
	 * @param {Array<Array<number>>} input - A 2-d matrix of data in row-major form [ A | b ]
	 * @param {number} order - How many degrees to solve for
	 *
	 * @return {Array<number>} - Vector of normalized solution coefficients matrix (x)
	 */
	function gaussianElimination(input, order) {
    const matrix = input
    const n = input.length - 1
    const coefficients = [order]

    for (let i = 0; i < n; i++) {
      let maxrow = i
      for (let j = i + 1; j < n; j++) {
				if (Math.abs(matrix[i][j]) > Math.abs(matrix[i][maxrow])) {
					maxrow = j;
				}
			}

			for (let k = i; k < n + 1; k++) {
        const tmp = matrix[k][i]
        matrix[k][i] = matrix[k][maxrow];
				matrix[k][maxrow] = tmp;
			}

			for (let _j = i + 1; _j < n; _j++) {
				for (let _k = n; _k >= i; _k--) {
					matrix[_k][_j] -= matrix[_k][i] * matrix[i][_j] / matrix[i][i];
				}
			}
		}

		for (let _j2 = n - 1; _j2 >= 0; _j2--) {
      let total = 0
      for (let _k2 = _j2 + 1; _k2 < n; _k2++) {
				total += matrix[_k2][_j2] * coefficients[_k2];
			}

			coefficients[_j2] = (matrix[n][_j2] - total) / matrix[_j2][_j2];
		}

		return coefficients;
	}

	/**
	 * Round a number to a precision, specified in number of decimal places
	 *
	 * @param {number} number - The number to round
	 * @param {number} precision - The number of decimal places to round to:
	 *                             > 0 means decimals, < 0 means powers of 10
	 *
	 *
	 * @return {number} - The number, rounded
	 */
	function round(number, precision) {
    const factor = Math.pow(10, precision)
    return Math.round(number * factor) / factor;
	}

  /**
   * The set of all fitting methods
   *
   * @namespace
   */
  const methods = {
    linear: function linear (data, options) {
      const sum = [0, 0, 0, 0, 0]
      let len = 0

      for (let n = 0; n < data.length; n++) {
        if (data[n][1] !== null) {
          len++
          sum[0] += data[n][0]
          sum[1] += data[n][1]
          sum[2] += data[n][0] * data[n][0]
          sum[3] += data[n][0] * data[n][1]
          sum[4] += data[n][1] * data[n][1]
        }
      }

      const run = len * sum[2] - sum[0] * sum[0]
      const rise = len * sum[3] - sum[0] * sum[1]
      const gradient = run === 0 ? 0 : round(rise / run, options.precision)
      const intercept = round(sum[1] / len - gradient * sum[0] / len, options.precision)

      const predict = function predict (x) {
        return [round(x, options.precision), round(gradient * x + intercept, options.precision)]
      }

      const points = data.map(function (point) {
        return predict(point[0])
      })

      return {
        points: points,
        predict: predict,
        equation: [gradient, intercept],
        r2: round(determinationCoefficient(data, points), options.precision),
        string: intercept === 0 ? 'y = ' + gradient + 'x' : 'y = ' + gradient + 'x + ' + intercept
      }
    },
    exponential: function exponential (data, options) {
      const sum = [0, 0, 0, 0, 0, 0]

      for (var n = 0; n < data.length; n++) {
        if (data[n][1] !== null) {
          sum[0] += data[n][0]
          sum[1] += data[n][1]
          sum[2] += data[n][0] * data[n][0] * data[n][1]
          sum[3] += data[n][1] * Math.log(data[n][1])
          sum[4] += data[n][0] * data[n][1] * Math.log(data[n][1])
          sum[5] += data[n][0] * data[n][1]
        }
      }

      const denominator = sum[1] * sum[2] - sum[5] * sum[5]
      const a = Math.exp((sum[2] * sum[3] - sum[5] * sum[4]) / denominator)
      const b = (sum[1] * sum[4] - sum[5] * sum[3]) / denominator
      const coeffA = round(a, options.precision)
      const coeffB = round(b, options.precision)
      const predict = function predict (x) {
        return [round(x, options.precision), round(coeffA * Math.exp(coeffB * x), options.precision)]
      }

      const points = data.map(function (point) {
        return predict(point[0])
      })

      return {
        points: points,
        predict: predict,
        equation: [coeffA, coeffB],
        string: 'y = ' + coeffA + 'e^(' + coeffB + 'x)',
        r2: round(determinationCoefficient(data, points), options.precision)
      }
    },
    logarithmic: function logarithmic (data, options) {
      const sum = [0, 0, 0, 0]
      const len = data.length

      for (let n = 0; n < len; n++) {
        if (data[n][1] !== null) {
          sum[0] += Math.log(data[n][0])
          sum[1] += data[n][1] * Math.log(data[n][0])
          sum[2] += data[n][1]
          sum[3] += Math.pow(Math.log(data[n][0]), 2)
        }
      }

      const a = (len * sum[1] - sum[2] * sum[0]) / (len * sum[3] - sum[0] * sum[0])
      const coeffB = round(a, options.precision)
      const coeffA = round((sum[2] - coeffB * sum[0]) / len, options.precision)

      const predict = function predict (x) {
        return [round(x, options.precision), round(round(coeffA + coeffB * Math.log(x), options.precision), options.precision)]
      }

      const points = data.map(function (point) {
        return predict(point[0])
      })

      return {
        points: points,
        predict: predict,
        equation: [coeffA, coeffB],
        string: 'y = ' + coeffA + ' + ' + coeffB + ' ln(x)',
        r2: round(determinationCoefficient(data, points), options.precision)
      }
    },
    power: function power (data, options) {
      const sum = [0, 0, 0, 0, 0]
      const len = data.length

      for (let n = 0; n < len; n++) {
        if (data[n][1] !== null) {
          sum[0] += Math.log(data[n][0])
          sum[1] += Math.log(data[n][1]) * Math.log(data[n][0])
          sum[2] += Math.log(data[n][1])
          sum[3] += Math.pow(Math.log(data[n][0]), 2)
        }
      }

      const b = (len * sum[1] - sum[0] * sum[2]) / (len * sum[3] - Math.pow(sum[0], 2))
      const a = (sum[2] - b * sum[0]) / len
      const coeffA = round(Math.exp(a), options.precision)
      const coeffB = round(b, options.precision)

      const predict = function predict (x) {
        return [round(x, options.precision), round(round(coeffA * Math.pow(x, coeffB), options.precision), options.precision)]
      }

      const points = data.map(function (point) {
        return predict(point[0])
      })

      return {
        points: points,
        predict: predict,
        equation: [coeffA, coeffB],
        string: 'y = ' + coeffA + 'x^' + coeffB,
        r2: round(determinationCoefficient(data, points), options.precision)
      }
    },
    polynomial: function polynomial (data, options) {
      const lhs = []
      const rhs = []
      let a = 0
      let b = 0
      const len = data.length
      const k = options.order + 1

      for (let i = 0; i < k; i++) {
        for (let l = 0; l < len; l++) {
          if (data[l][1] !== null) {
            a += Math.pow(data[l][0], i) * data[l][1]
          }
        }

        lhs.push(a)
        a = 0

        const c = []
        for (let j = 0; j < k; j++) {
          for (let _l = 0; _l < len; _l++) {
            if (data[_l][1] !== null) {
              b += Math.pow(data[_l][0], i + j)
            }
          }
          c.push(b)
          b = 0
        }
        rhs.push(c)
      }
      rhs.push(lhs)

      const coefficients = gaussianElimination(rhs, k).map(function (v) {
        return round(v, options.precision)
      })

      const predict = function predict (x) {
        return [round(x, options.precision), round(coefficients.reduce(function (sum, coeff, power) {
          return sum + coeff * Math.pow(x, power)
        }, 0), options.precision)]
      }

      const points = data.map(function (point) {
        return predict(point[0])
      })

      let string = 'y = '
      for (let _i = coefficients.length - 1; _i >= 0; _i--) {
        if (_i > 1) {
          string += coefficients[_i] + 'x^' + _i + ' + '
        } else if (_i === 1) {
          string += coefficients[_i] + 'x + '
        } else {
          string += coefficients[_i]
        }
      }

      return {
        string: string,
        points: points,
        predict: predict,
        equation: [].concat(_toConsumableArray(coefficients)).reverse(),
        r2: round(determinationCoefficient(data, points), options.precision)
      }
    }
  }

  function createWrapper() {
    const reduce = function reduce (accumulator, name) {
      return _extends({
        _round: round
      }, accumulator, _defineProperty({}, name, function (data, supplied) {
        return methods[name](data, _extends({}, DEFAULT_OPTIONS, supplied))
      }))
    }

    return Object.keys(methods).reduce(reduce, {});
	}

	module.exports = createWrapper();
});
