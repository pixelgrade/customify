(function ($, exports, wp) {
  const api = wp.customize

  function swap_values (setting_one, setting_two) {
    const color_primary = api(setting_one)()
    const color_secondary = api(setting_two)()

    api(setting_one).set(color_secondary)
    api(setting_two).set(color_primary)
  }

  api.bind('ready', function () {
    const $document = $(document)

    $document.on('click', '[data-action="sm_swap_colors"]', function (e) {
      e.preventDefault()
      swap_values('sm_color_primary', 'sm_color_secondary')
    })

    $document.on('click', '[data-action="sm_swap_dark_light"]', function (e) {
      e.preventDefault()
      swap_values('sm_dark_primary', 'sm_light_primary')
      swap_values('sm_dark_secondary', 'sm_light_secondary')
      swap_values('sm_dark_tertiary', 'sm_light_tertiary')
    })

    $document.on('click', '[data-action="sm_swap_colors_dark"]', function (e) {
      e.preventDefault()
      swap_values('sm_color_primary', 'sm_dark_primary')
      swap_values('sm_color_secondary', 'sm_dark_secondary')
      swap_values('sm_color_tertiary', 'sm_dark_tertiary')
    })

    $document.on('click', '[data-action="sm_swap_secondary_colors_dark"]', function (e) {
      e.preventDefault()
      swap_values('sm_color_secondary', 'sm_dark_secondary')
    })

  })

})(jQuery, window, wp)
