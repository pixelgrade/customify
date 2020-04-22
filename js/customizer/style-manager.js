/** @namespace customify */
window.customify = window.customify || parent.customify || {};

(function ($, exports, wp, document) {

  /**
   * Expose the API publicly on window.customify.styleManager
   *
   * @namespace customify.styleManager
   */
  if (typeof customify.styleManager === 'undefined') {
    customify.styleManager = {}
  }
  _.extend( customify.styleManager, function () {

    const api = wp.customize

    api.bind('ready', function () {

      // Handle the Style Manager user feedback logic.
      const $userFeedbackModal = $('#style-manager-user-feedback-modal')
      if ($userFeedbackModal.length) {
        const $userFeedbackForm = $userFeedbackModal.find('form'),
          $userFeedbackCloseBtn = $userFeedbackModal.find('.close'),
          $userFeedbackFirstStep = $userFeedbackModal.find('.first-step'),
          $userFeedbackSecondStep = $userFeedbackModal.find('.second-step'),
          $userFeedbackThanksStep = $userFeedbackModal.find('.thanks-step'),
          $userFeedbackErrorStep = $userFeedbackModal.find('.error-step')

        let userFeedbackModalShown = false,
          colorPaletteChanged = false,
          fontPaletteChanged = false

        // Handle when to open the modal.
        api.bind('saved', function () {
          // We will only show the modal once per Customizer session.
          if (!userFeedbackModalShown && (colorPaletteChanged || fontPaletteChanged)) {
            $('body').addClass('feedback-modal-open modal-open')
            userFeedbackModalShown = true
          }
        })

        // Handle the color palette changed info update.
        const colorPaletteSetting = api('sm_color_palette')
        if (!_.isUndefined(colorPaletteSetting)) {
          colorPaletteSetting.bind(function (new_value, old_value) {
            // Intentional loose comparison.
            if (new_value != old_value) {
              colorPaletteChanged = true
            }
          })
        }
        const colorPaletteVariationSetting = api('sm_color_palette_variation')
        if (!_.isUndefined(colorPaletteVariationSetting)) {
          colorPaletteVariationSetting.bind(function (new_value, old_value) {
            // Intentional loose comparison.
            if (new_value != old_value) {
              colorPaletteChanged = true
            }
          })
        }

        // Handle the font palette changed info update.
        const fontPaletteSetting = api('sm_font_palette')
        if (!_.isUndefined(fontPaletteSetting)) {
          fontPaletteSetting.bind(function (new_value, old_value) {
            // Intentional loose comparison.
            if (new_value != old_value) {
              fontPaletteChanged = true
            }
          })
        }

        // Handle the modal submit.
        $userFeedbackForm.on('submit', function (event) {
          event.preventDefault()

          let $form = $(event.target)

          let data = {
            action: 'customify_style_manager_user_feedback',
            nonce: customify.styleManager.userFeedback.nonce,
            type: $form.find('input[name=type]').val(),
            rating: $form.find('input[name=rating]:checked').val(),
            message: $form.find('textarea[name=message]').val()
          }

          $.post(
            customify.config.ajax_url,
            data,
            function (response) {
              if (true === response.success) {
                $userFeedbackFirstStep.hide()
                $userFeedbackSecondStep.hide()
                $userFeedbackThanksStep.show()
                $userFeedbackErrorStep.hide()
              } else {
                $userFeedbackFirstStep.hide()
                $userFeedbackSecondStep.hide()
                $userFeedbackThanksStep.hide()
                $userFeedbackErrorStep.show()
              }
            }
          )
        })

        $userFeedbackForm.find('input[name=rating]').on('change', function (event) {
          // Leave everything in working order
          setTimeout(function () {
            $userFeedbackSecondStep.show()
          }, 300)

          let rating = $userFeedbackForm.find('input[name=rating]:checked').val()

          $userFeedbackForm.find('.rating-placeholder').text(rating)
        })

        $userFeedbackCloseBtn.on('click', function (event) {
          event.preventDefault()

          $('body').removeClass('feedback-modal-open modal-open')

          // Leave everything in working order
          setTimeout(function () {
            $userFeedbackFirstStep.show()
            $userFeedbackSecondStep.hide()
            $userFeedbackThanksStep.hide()
            $userFeedbackErrorStep.hide()
          }, 300)
        })
      }
    })

    // Reverses a hex color to either black or white
    const inverseHexColorToBlackOrWhite = function (hex) {
      return inverseHexColor(hex, true)
    }

    // Taken from here: https://stackoverflow.com/a/35970186/6260836
    const inverseHexColor = function (hex, bw) {
      if (hex.indexOf('#') === 0) {
        hex = hex.slice(1)
      }
      // convert 3-digit hex to 6-digits.
      if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]
      }
      if (hex.length !== 6) {
        throw new Error('Invalid HEX color.')
      }
      let r = parseInt(hex.slice(0, 2), 16),
        g = parseInt(hex.slice(2, 4), 16),
        b = parseInt(hex.slice(4, 6), 16)
      if (bw) {
        // http://stackoverflow.com/a/3943023/112731
        return (
          r * 0.299 + g * 0.587 + b * 0.114
        ) > 186
          ? '#000000'
          : '#FFFFFF'
      }
      // invert color components
      r = (
        255 - r
      ).toString(16)
      g = (
        255 - g
      ).toString(16)
      b = (
        255 - b
      ).toString(16)
      // pad each with zeros and return
      return '#' + padZero(r) + padZero(g) + padZero(b)
    }

    const padZero = function (str, len) {
      len = len || 2
      const zeros = new Array(len).join('0')
      return (
        zeros + str
      ).slice(-len)
    }

    return {
      inverseHexColor: inverseHexColor,
      inverseHexColorToBlackOrWhite: inverseHexColorToBlackOrWhite,
      padZero: padZero,
    }
  }() )

})(jQuery, window.customify, wp, document)
