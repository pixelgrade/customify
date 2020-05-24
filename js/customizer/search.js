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

    let fuse = null

    const init = function () {

      const searchableControls = _.map(api.settings.controls, function (control, controlId) {
        if ( typeof controlId !== 'string') {
          controlId = String(controlId)
        }
        // Determine if the control should be excluded from search results.
        const excluded = _.find( customify.search.excludedControls, function (partial) {
          return controlId.indexOf(partial) !== -1;
        })
        if (excluded !== undefined) {
          return
        }

        const searchableControl = {
          "label": (typeof control.label !== 'undefined' && !_.isEmpty(control.label)) ? control.label : '',
          "description": (typeof control.description !== 'undefined' && !_.isEmpty(control.description)) ? control.description : '',
          "panelName": '',
          "sectionName": '',
          "panel": null,
          "section": control.section, // This is to know what section to expand when clicking on this result.
        }

        _.map(api.settings.sections, function (section, index) {
          if (control.section === section.id) {
            _.map(_wpCustomizeSettings.panels, function (panel, index) {
              if ('' === section.panel) {
                searchableControl.panelName = section.title
              }

              if (section.panel === panel.id) {
                searchableControl.sectionName = section.title
                searchableControl.panel = section.panel
                searchableControl.panelName = panel.title
              }
            })
          }
        })

        return searchableControl
      }).filter(function(item){ // Make sure that we remove excluded controls entries.
        return item !== undefined;
      })

      // Initialize the FuseJS search
      const fuseOptions = {
        includeScore: true,
        includeMatches: true,
        shouldSort: true,
        minMatchCharLength: 2,
        threshold: 0.3, // The fussy search threshold. Lower for closer matches (less fuzzy).
        keys: [
          {
            name: 'label',
            weight: 1
          },
          {
            name: 'description',
            weight: 0.8
          },
          {
            name: 'panelName',
            weight: 0.4
          },
          {
            name: 'sectionName',
            weight: 0.4
          }
        ]
      }

      // Create a new instance of Fuse
      fuse = new Fuse(searchableControls, fuseOptions)

      const $customizeInfo = $('#customize-info')

      customizePanelsParent = $('#customize-theme-controls')
      customizePanelsParent.after('<div id="customify-search-results"></div>')

      $customizeInfo.on('keyup', searchInputSelector, function (event) {
        event.preventDefault()

        const searchString = $(searchInputSelector).val()

        // At least 3 characters required for search.
        if (searchString.length > 2) {
          displayResults(searchString)
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

      // Handle showing the current search results when returning to the top pane.
      api.state( 'expandedSection' ).bind(showSearchResultsWhenTopPaneVisible)
      api.state( 'expandedPanel' ).bind(showSearchResultsWhenTopPaneVisible)
    }

    const showSearchResultsWhenTopPaneVisible = function() {
      if (!api.state( 'expandedSection' ).get() && !api.state( 'expandedPanel' ).get()) {
        const searchString = $(searchInputSelector).val()
        if (searchString.length > 2) {
          setTimeout( function() {
            displayResults(searchString)
          }, 400)
        }
      }
    }

    const displayResults = function (stringToSearch) {
      const resultsArray = fuse.search(stringToSearch)

      // Bail if no results.
      if (0 === resultsArray.length) {
        customizePanelsParent.removeClass('search-found')
        return
      }

      const html = resultsArray.map(function (result, index) {

        // Bail if no matches or empty label.
        if (_.isEmpty(result.matches) || '' === result.item.label) {
          return
        }

        // Make a copy for highlight.
        const highlightedResult =$.extend(true, {}, result);

        // Highlight everything there is to highlight.
        _.each(result.matches, function(match) {
          if (typeof match.indices === 'undefined' || _.isEmpty(match.indices)) {
            // No highlighting to do.
            return
          }

          // The key is like `label` or `sectionName`.
          highlightedResult.item[match.key] = generateHighlightedText(match.value, match.indices)
        })

        // Construct the control trail with panel > section.
        let controlTrail = highlightedResult.item.panelName
        if ('' !== highlightedResult.item.sectionName) {
          controlTrail = `${controlTrail} ▸ ${highlightedResult.item.sectionName}`
        }

        return `
                <li id="accordion-section-${result.item.section}" class="accordion-section control-section control-section-default customizer-search-results" aria-owns="sub-accordion-section-${result.item.section}" data-section="${result.item.section}">
                    <h3 class="accordion-section-title" tabindex="0">
                        ${highlightedResult.item.label}
                        <span class="screen-reader-text">${customify.l10n.search.resultsSectionScreenReaderText}</span>
                    </h3>
                    <span class="search-setting-path">${controlTrail}</i></span>
                </li>
                `
      }).join('')

      customizePanelsParent.addClass('search-found')
      document.getElementById('customify-search-results').innerHTML = `<ul>${html}</ul>`

      const searchSettings = document.querySelectorAll('#customify-search-results .accordion-section')
      searchSettings.forEach(setting => setting.addEventListener('click', expandSection))
    }

    // Does not account for overlapping highlighted regions, if that exists at all O_o..
    const generateHighlightedText = function (text, regions) {
      if(!regions) {
        return text;
      }

      const highlightedText = []
      let pair = regions.shift()
      // Build the formatted string
      for (let i = 0; i < text.length; i++) {
        const char = text.charAt(i)
        if (pair && i == pair[0]) {
          highlightedText.push('<span class="hl">')
        }
        highlightedText.push(char)
        if (pair && i == pair[1]) {
          highlightedText.push('</span>')
          pair = regions.shift()
        }
      }

      return highlightedText.join('')
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

    const expandSection = function (event) {
      const sectionName = this.getAttribute('data-section')
      const section = api.section(sectionName)

      customizePanelsParent.removeClass('search-found')
      document.getElementById('customify-search-results').innerHTML = ''
      $(searchInputSelector).focus()

      section.expand()
    }

    /**
     * Clear Search input and display all the options.
     */
    const clearSearch = function () {
      customizePanelsParent.removeClass('search-found')
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
