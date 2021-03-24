(function ($) {

  'use strict'
  $(function () {

    $(document).ready(function () {
      $('#reset_theme_mods').on('click', function () {
        let confirm = window.confirm('Are you sure?');

        if ( ! confirm ) {
          return false;
        }

        $.ajax({
          url: customify.config.wp_rest.root + 'customify/v1/delete_theme_mod',
          method: 'POST',
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', customify.config.wp_rest.nonce);
          },
          data: {
            'customify_settings_nonce': customify.config.wp_rest.customify_settings_nonce
          }
        }).done(function (response) {
          if ( response.success ) {
            alert( 'Success: ' + response.data );
          } else {
            alert( 'No luck: ' + response.data );
          }
        }).error(function (e) {
          console.log(e);
        });
      });

      /* Ensure groups visibility */
      $('.switch input[type=checkbox], .select select').each(function () {

        if ($(this).data('show_group')) {

          let show = false
          if ($(this).attr('checked')) {
            show = true
          } else if (typeof $(this).data('display_option') !== 'undefined' && $(this).data('display_option') === $(this).val()) {
            show = true
          }

          toggleGroup($(this).data('show_group'), show)
        }
      })

      $('.switch, .select ').on('change', 'input[type=checkbox], select', function () {
        if ($(this).data('show_group')) {
          let show = false
          if ($(this).attr('checked')) {
            show = true
          } else if (typeof $(this).data('display_option') !== 'undefined' && $(this).data('display_option') === $(this).val()) {
            show = true
          }
          toggleGroup($(this).data('show_group'), show)
        }
      })
    });

    const toggleGroup = function (name, show) {
      const $group = $('#' + name)

      if (show) {
        $group.show()
      } else {
        $group.hide()
      }
    }
  })

  $.fn.check_for_extended_options = function () {
    const extended_options = $(this).siblings('fieldset.group')
    if ($(this).data('show-next')) {
      if (extended_options.data('extended') === true) {
        extended_options
          .data('extended', false)
          .css('height', '0')
      } else if ((typeof extended_options.data('extended') === 'undefined' && $(this).attr('checked') === 'checked') || extended_options.data('extended') === false) {
        extended_options
          .data('extended', true)
          .css('height', 'auto')
      }
    }
  }

}(jQuery))
