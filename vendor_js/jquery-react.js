/**
 * React JQuery plugin
 * https://github.com/natedavisolds/jquery-react
 *
 * Copyright 2011, Nathan Davis Olds
 * @license MIT
 * https://github.com/natedavisolds/jquery-react/blob/master/license
 */

(function ($) {

  const React = {
    reactTo: function (selector) {
      const $elements = $(selector),
        $reactor_element = $(this)

      const _proxy_event = function () {
        $reactor_element.trigger('react.reactor')
      }

      $elements.filter(':not(:text), :not(:password)').on('change.reactor', _proxy_event)
      $elements.filter(':text.date-picker').on('change.reactor', _proxy_event)
      $elements.filter(':text').on('keyup.reactor', _proxy_event)
      $elements.filter(':password').on('keyup.reactor', _proxy_event)

      return this
    },

    reactIf: function (selector, exp_func) {
      const $sel = $(selector),
        args = Array.prototype.slice.call(arguments, 2)

      const _func = function () {
        if ($.isFunction(exp_func)) {
          return exp_func.apply($sel)
        } else {
          const _returned = $.fn.reactor.helpers[exp_func].apply($sel, args)

          if ($.isFunction(_returned)) {
            return _returned.apply($sel)
          } else {
            return _returned
          }
        }
      }

      this.each(function () {
        const $reactor = $(this)

        if (!$reactor.hasClass('reactor')) {
          $reactor.reactor()
        }

        let conditions = $reactor.data('conditions.reactor')
        if (!$.isArray(conditions)) { conditions = [] }

        conditions.push(_func)

        $(this).data('conditions.reactor', conditions)
      })

      $(this).reactTo(selector)

      return this
    },

    react: function () {
      this.each(function () {
        $(this).trigger('react.reactor')
      })

      return this
    },

    reactor: function (options) {
      const settings = $.extend({}, $.fn.reactor.defaults, options)

      this.each(function () {
        const $element = $(this)

        if (!$.isArray($element.data('conditions.reactor'))) {
          $element
            .data('conditions.reactor', [])
            .addClass('reactor')
        }

        const isReactionary = function () {
          const conditionalArray = $(this).data('conditions.reactor')
          let r = true

          $.each(conditionalArray, function () {
            r = this.call()
            return r // short circuits the loop when any value is false
          })

          return r
        }

        const reaction = function (evt) {
          if (isReactionary.apply(this)) {
            settings.compliant.apply($element)
          } else {
            settings.uncompliant.apply($element)
          }
        }

        $element.on('react.reactor', reaction)
      })

      return this
    }
  }

  $.fn.reactTo = React.reactTo
  $.fn.reactIf = React.reactIf
  $.fn.react = React.react
  $.fn.reactor = React.reactor

  $.fn.reactor.defaults = {
    compliant: function () {
      $(this).show()
    },
    uncompliant: function () {
      $(this).hide()
    }
  }

  $.fn.reactor.helpers = {
    NotBlank: function () {
      return (this.val().toString() != '')
    },

    Blank: function () {
      return (this.val().toString() == '')
    },

    HasElements: function () {
      return this.size() > 0
    },

    Disabled: function () {
      return (this.filter(':disabled').length > 0)
    },

    Enabled: function () {
      return (this.filter(':enabled').length > 0)
    },

    IsChecked: function () {
      return this.is(':checked')
    },

    IsNotChecked: function () {
      return !this.is(':checked')
    },

    EqualTo: function (matchStr) {
      const _func = function () {
        const v = this.val()
        if (v) {
          return (v.toString() == matchStr.toString())
        } else {
          return false
        }
      }
      return _func
    },

    NotEqualTo: function (matchStr) {
      const _func = function () {
        const v = this.val()

        if (v) {
          return (v.toString() != matchStr.toString())
        } else {
          return true
        }
      }
      return _func
    },

    NumberOfDigitsIs: function (number) {
      let comparisonString = this.val().toString().replace(/[^\d]+/g, ''),
        passing = false,
        length = comparisonString.length

      for (index in arguments) {
        if (length == arguments[index]) {
          passing = true
        }
      }

      return passing
    },

    LessThan: function (number) {
      const _func = function () {
        const v = this.filter('span').length > 0 ? this.text() : this.val()
        return (parseInt(v) < number)
      }
      return _func
    },

    MoreThan: function (number) {
      const _func = function () {
        const v = this.filter('span').length > 0 ? this.text() : this.val(),
          result = (parseInt(v) > number)

        return (result)
      }
      return _func
    },

    Between: function (min, max) {
      const _func = function () {
        const v = this.val()
        return (!(v && (parseInt(v) > max || parseInt(v) < min)))
      }
      return _func
    },

    BetweenSameLength: function (min, max) {
      const len = min.toString().length
      const _func = function () {
        const v = this.val()
        return (!(v && v.length == len && (parseInt(v) > max || parseInt(v) < min)))
      }
      return _func
    },

    HasValueWhenVisible: function () {
      if (this.is(':visible')) {
        return (this.val().toString() != '' && parseFloat(this.val()) != 0.0)
      } else {
        return true
      }
    }
  }
})(jQuery)
