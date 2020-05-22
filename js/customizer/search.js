/**
 * The logic for the Customizer controls search.
 *
 * Based on the logic from this WordPress plugin: https://wordpress.org/plugins/customizer-search/
 */

/** @namespace customify */
window.customify = window.customify || parent.customify || {};

(function ($, customify, wp) {

  /**
   * Expose the API publicly on window.customify.search
   *
   * @namespace customify.search
   */
  if (typeof customify.search === 'undefined') {
    customify.search = {}
  }

  _.extend(customify.search, function () {
    const api = wp.customize

    const searchWrapperSelector = '#accordion-section-customify-customizer-search'
    const searchInputSelector = '#customify-customizer-search-input'

    let customizePanelsParent = null

    const init = function () {

      const excludedControls = [
        // Color Palettes
        'sm_dark_color_master_slider',
        'sm_dark_color_primary_slider',
        'sm_dark_color_secondary_slider',
        'sm_dark_color_tertiary_slider',
        'sm_colors_dispersion',
        'sm_colors_focus_point',
        'sm_color_palette',
        'sm_color_palette_variation',
        'sm_color_primary',
        'sm_color_primary_final',
        'sm_color_secondary',
        'sm_color_secondary_final',
        'sm_color_tertiary',
        'sm_color_tertiary_final',
        'sm_dark_primary',
        'sm_dark_primary_final',
        'sm_dark_secondary',
        'sm_dark_secondary_final',
        'sm_dark_tertiary',
        'sm_dark_tertiary_final',
        'sm_light_primary',
        'sm_light_primary_final',
        'sm_light_secondary',
        'sm_light_secondary_final',
        'sm_light_tertiary',
        'sm_light_tertiary_final',
        'sm_swap_colors',
        'sm_swap_dark_light',
        'sm_swap_colors_dark',
        'sm_swap_secondary_colors_dark',
        'sm_advanced_toggle',
        'sm_spacing_bottom',
        // Font Palettes
        'sm_font_palette',
        'sm_font_palette_variation',
        'sm_font_primary',
        'sm_font_secondary',
        'sm_font_body',
        'sm_font_accent',
        'sm_swap_fonts',
        'sm_swap_primary_secondary_fonts',
      ]

      const controls = _.map(api.settings.controls, function (control, controlId) {
        if ( typeof controlId !== 'string') {
          controlId = String(controlId)
        }
        // See if the control should be excluded
        const excluded = _.find( excludedControls, function (partial) {
          return controlId.indexOf(partial) !== -1;
        })
        if (excluded !== undefined) {
          return
        }

        if (typeof control.description === 'undefined' || _.isEmpty(control.description)) {
          control.description = "";
        }
        _.map(api.settings.sections, function (section, index) {
          if (control.section === section.id) {
            _.map(_wpCustomizeSettings.panels, function (panel, index) {
              if ('' === section.panel) {
                control.panelName = section.title
              }

              if (section.panel === panel.id) {
                control.sectionName = section.title
                control.panel = section.panel
                control.panelName = panel.title
              }
            })
          }
        })

        return control
      }).filter(function(item){ // Make sure that we remove excluded controls entries.
        return item !== undefined;
      })

      const $customizeInfo = $('#customize-info')

      customizePanelsParent = $('#customize-theme-controls')
      customizePanelsParent.after('<div id="customify-search-results"></div>')

      $customizeInfo.on('keyup', searchInputSelector, function (event) {
        event.preventDefault()

        const searchString = $(searchInputSelector).val()

        // At least 3 characters required for search.
        if (searchString.length > 2) {
          displayMatches(searchString, controls)
        } else if (searchString.length === 0) {
          clearSearch()
        }
      })

      $customizeInfo.on('click', '.clear-search', function (event) {
        clearSearch()
      })

      $customizeInfo.on('click', '.close-search', function (event) {
        toggleDisplaySearchForm()
      })

      $customizeInfo.on('click', '.customize-search-toggle', function (event) {
        toggleDisplaySearchForm()
      })

      api.previewer.targetWindow.bind(showSearchButtonToggle)
    }

    const expandSection = function (setting) {
      const sectionName = this.getAttribute('data-section')
      const section = api.section(sectionName)

      clearSearch()
      section.expand()
    }

    const displayMatches = function (stringToMatch, controls) {
      const matchArray = findMatches(stringToMatch, controls)

      // Bail if no results.
      if (0 === matchArray.length) {
        customizePanelsParent.removeClass('search-found').addClass('search-not-found')
        return
      }

      const html = matchArray.map(function (index, elem) {

        // Bail if no results.
        if ('' === index.label) {
          return
        }

        let settingTrail = index.panelName
        if ('' !== index.sectionName) {
          settingTrail = `${settingTrail} ▸ ${index.sectionName}`
        }

        const regex = new RegExp(stringToMatch, 'gi')

        const label = replaceCase(index.label, regex, stringToMatch)
        settingTrail = replaceCase(settingTrail, regex, stringToMatch)

        return `
                <li id="accordion-section-${index.section}" class="accordion-section control-section control-section-default customizer-search-results" aria-owns="sub-accordion-section-${index.section}" data-section="${index.section}">
                    <h3 class="accordion-section-title" tabindex="0">
                        ${label}
                        <span class="screen-reader-text">Press return or enter to open this section</span>
                    </h3>
                    <span class="search-setting-path">${settingTrail}</i></span>
                </li>
                `
      }).join('')

      customizePanelsParent.removeClass('search-not-found').addClass('search-found')
      document.getElementById('customify-search-results').innerHTML = `<ul id="customizer-search-results">${html}</ul>`

      const searchSettings = document.querySelectorAll('#customify-search-results .accordion-section')
      searchSettings.forEach(setting => setting.addEventListener('click', expandSection))
    }

    const replaceCase = function (str, regex, newStr) {
      const replacer = (c, i) => c.match(/[A-Z]/) ? newStr[i].toUpperCase() : newStr[i]
      return str.replace(regex, (oldStr) => '<span class="hl">' + oldStr.replace(/./g, replacer) + '</span>' )
    }

    const findMatches = function (stringToMatch, controls) {
      return controls.filter(control => {

        if (control.panelName == null) control.panelName = ''
        if (control.sectionName == null) control.sectionName = ''

        // Search for the stringToMatch from control label, Panel Name, Section Name.
        const regex = new RegExp(stringToMatch, 'gi')
        return control.label.match(regex) || control.description.match(regex) || control.panelName.match(regex) || control.sectionName.match(regex)
      })
    }

    /**
     * Shows the message that is shown for when a header
     * or footer is already set for this page.
     */
    const showSearchButtonToggle = function () {
      let template = wp.template('customify-search-button')
      if ($('#customize-info .accordion-section-title .customize-search-toggle').length === 0) {
        $('#customize-info .accordion-section-title').append(template())
      }

      template = wp.template('customify-search-form')
      if ($('#customize-info '+searchWrapperSelector).length === 0) {
        $('#customize-info .customize-panel-description').after(template())
      }
    }

    const toggleDisplaySearchForm = function () {
      const $wrapper = $(searchWrapperSelector)

      if ($wrapper.hasClass('open')) {
        // Close it
        $wrapper.removeClass('open')
        $wrapper.slideUp('fast')

        // Also clear the search.
        clearSearch()
      } else {
        // Open it
        $('.customize-panel-description').removeClass('open')
        $('.customize-panel-description').slideUp('fast')

        $wrapper.addClass('open')
        $wrapper.slideDown('fast')

        $(searchInputSelector).focus()
      }
    }

    /**
     * Clear Search input and display all the options.
     */
    const clearSearch = function () {
      customizePanelsParent.removeClass('search-not-found search-found')
      document.getElementById('customify-search-results').innerHTML = ''
      document.getElementById('customify-customizer-search-input').value = ''

      $(searchInputSelector).focus()
    }

    // When the customizer is ready prepare the search logic.
    api.bind('ready', init)

    return {
      init: init,
    }
  }())

})(jQuery, customify, wp)
