/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/_js/customizer-search/index.js":
/*!********************************************!*\
  !*** ./src/_js/customizer-search/index.js ***!
  \********************************************/
/***/ (function() {

eval("/**\n * The logic for the Customizer controls search.\n *\n * Based on the logic from this WordPress plugin: https://wordpress.org/plugins/customizer-search/\n */\n\n/** @namespace customify */\nwindow.customify = window.customify || parent.customify || {};\n\n(function ($, customify, wp) {\n  /**\n   * Expose the API publicly on window.customify.search\n   *\n   * @namespace customify.search\n   */\n  if (typeof customify.search === 'undefined') {\n    customify.search = {};\n  }\n\n  _.extend(customify.search, function () {\n    var api = wp.customize;\n    var searchWrapperSelector = '#accordion-section-customify-customizer-search';\n    var searchInputSelector = '#customify-customizer-search-input';\n    var customizePanelsParent = null;\n    var fuse = null;\n\n    var init = function init() {\n      var searchableControls = _.map(api.settings.controls, function (control, controlId) {\n        if (typeof controlId !== 'string') {\n          controlId = String(controlId);\n        } // Determine if the control should be excluded from search results.\n\n\n        var excluded = _.find(customify.search.excludedControls, function (partial) {\n          return controlId.indexOf(partial) !== -1;\n        });\n\n        if (excluded !== undefined) {\n          return;\n        }\n\n        var searchableControl = {\n          \"label\": typeof control.label !== 'undefined' && !_.isEmpty(control.label) ? control.label : '',\n          \"description\": typeof control.description !== 'undefined' && !_.isEmpty(control.description) ? control.description : '',\n          \"panelName\": '',\n          \"sectionName\": '',\n          \"panel\": null,\n          \"section\": control.section // This is to know what section to expand when clicking on this result.\n\n        };\n\n        _.map(api.settings.sections, function (section, index) {\n          if (control.section === section.id) {\n            _.map(_wpCustomizeSettings.panels, function (panel, index) {\n              if ('' === section.panel) {\n                searchableControl.panelName = section.title;\n              }\n\n              if (section.panel === panel.id) {\n                searchableControl.sectionName = section.title;\n                searchableControl.panel = section.panel;\n                searchableControl.panelName = panel.title;\n              }\n            });\n          }\n        });\n\n        return searchableControl;\n      }).filter(function (item) {\n        // Make sure that we remove excluded controls entries.\n        return item !== undefined;\n      }); // Initialize the FuseJS search\n\n\n      var fuseOptions = {\n        includeScore: true,\n        includeMatches: true,\n        shouldSort: true,\n        minMatchCharLength: 2,\n        threshold: 0.3,\n        // The fussy search threshold. Lower for closer matches (less fuzzy).\n        keys: [{\n          name: 'label',\n          weight: 1\n        }, {\n          name: 'description',\n          weight: 0.8\n        }, {\n          name: 'panelName',\n          weight: 0.4\n        }, {\n          name: 'sectionName',\n          weight: 0.4\n        }]\n      }; // Create a new instance of Fuse\n\n      fuse = new Fuse(searchableControls, fuseOptions);\n      var $customizeInfo = $('#customize-info');\n      customizePanelsParent = $('#customize-theme-controls');\n      customizePanelsParent.after('<div id=\"customify-search-results\"></div>');\n      $customizeInfo.on('keyup', searchInputSelector, function (event) {\n        event.preventDefault();\n        var searchString = $(searchInputSelector).val(); // At least 3 characters required for search.\n\n        if (searchString.length > 2) {\n          displayResults(searchString);\n        } else if (searchString.length === 0) {\n          clearSearch();\n        }\n      });\n      $customizeInfo.on('click', '.clear-search', function (event) {\n        clearSearch();\n      });\n      $customizeInfo.on('click', '.close-search', function (event) {\n        toggleDisplaySearchForm();\n      });\n      $customizeInfo.on('click', '.customize-search-toggle', function (event) {\n        toggleDisplaySearchForm();\n      });\n      api.previewer.targetWindow.bind(showSearchButtonToggle); // Handle showing the current search results when returning to the top pane.\n\n      api.state('expandedSection').bind(showSearchResultsWhenTopPaneVisible);\n      api.state('expandedPanel').bind(showSearchResultsWhenTopPaneVisible);\n    };\n\n    var showSearchResultsWhenTopPaneVisible = function showSearchResultsWhenTopPaneVisible() {\n      if (!api.state('expandedSection').get() && !api.state('expandedPanel').get()) {\n        var searchString = $(searchInputSelector).val();\n\n        if (searchString.length > 2) {\n          setTimeout(function () {\n            displayResults(searchString);\n          }, 400);\n        }\n      }\n    };\n\n    var displayResults = function displayResults(stringToSearch) {\n      var resultsArray = fuse.search(stringToSearch); // Bail if no results.\n\n      if (0 === resultsArray.length) {\n        customizePanelsParent.removeClass('search-found');\n        return;\n      }\n\n      var html = resultsArray.map(function (result, index) {\n        // Bail if no matches or empty label.\n        if (_.isEmpty(result.matches) || '' === result.item.label) {\n          return;\n        } // Make a copy for highlight.\n\n\n        var highlightedResult = $.extend(true, {}, result); // Highlight everything there is to highlight.\n\n        _.each(result.matches, function (match) {\n          if (typeof match.indices === 'undefined' || _.isEmpty(match.indices)) {\n            // No highlighting to do.\n            return;\n          } // The key is like `label` or `sectionName`.\n\n\n          highlightedResult.item[match.key] = generateHighlightedText(match.value, match.indices);\n        }); // Construct the control trail with panel > section.\n\n\n        var controlTrail = highlightedResult.item.panelName;\n\n        if ('' !== highlightedResult.item.sectionName) {\n          controlTrail = \"\".concat(controlTrail, \" \\u25B8 \").concat(highlightedResult.item.sectionName);\n        }\n\n        return \"\\n                <li id=\\\"accordion-section-\".concat(result.item.section, \"\\\" class=\\\"accordion-section control-section control-section-default customizer-search-results\\\" aria-owns=\\\"sub-accordion-section-\").concat(result.item.section, \"\\\" data-section=\\\"\").concat(result.item.section, \"\\\">\\n                    <h3 class=\\\"accordion-section-title\\\" tabindex=\\\"0\\\">\\n                        \").concat(highlightedResult.item.label, \"\\n                        <span class=\\\"screen-reader-text\\\">\").concat(customify.l10n.search.resultsSectionScreenReaderText, \"</span>\\n                    </h3>\\n                    <span class=\\\"search-setting-path\\\">\").concat(controlTrail, \"</i></span>\\n                </li>\\n                \");\n      }).join('');\n      customizePanelsParent.addClass('search-found');\n      document.getElementById('customify-search-results').innerHTML = \"<ul>\".concat(html, \"</ul>\");\n      var searchSettings = document.querySelectorAll('#customify-search-results .accordion-section');\n      searchSettings.forEach(function (setting) {\n        return setting.addEventListener('click', expandSection);\n      });\n    }; // Does not account for overlapping highlighted regions, if that exists at all O_o..\n\n\n    var generateHighlightedText = function generateHighlightedText(text, regions) {\n      if (!regions) {\n        return text;\n      }\n\n      var highlightedText = [];\n      var pair = regions.shift(); // Build the formatted string\n\n      for (var i = 0; i < text.length; i++) {\n        var char = text.charAt(i);\n\n        if (pair && i == pair[0]) {\n          highlightedText.push('<span class=\"hl\">');\n        }\n\n        highlightedText.push(char);\n\n        if (pair && i == pair[1]) {\n          highlightedText.push('</span>');\n          pair = regions.shift();\n        }\n      }\n\n      return highlightedText.join('');\n    };\n    /**\n     * Shows the message that is shown for when a header\n     * or footer is already set for this page.\n     */\n\n\n    var showSearchButtonToggle = function showSearchButtonToggle() {\n      var template = wp.template('customify-search-button');\n\n      if ($('#customize-info .accordion-section-title .customize-search-toggle').length === 0) {\n        $('#customize-info .accordion-section-title').append(template());\n      }\n\n      template = wp.template('customify-search-form');\n\n      if ($('#customize-info ' + searchWrapperSelector).length === 0) {\n        $('#customize-info .customize-panel-description').after(template());\n      }\n    };\n\n    var toggleDisplaySearchForm = function toggleDisplaySearchForm() {\n      var $wrapper = $(searchWrapperSelector);\n\n      if ($wrapper.hasClass('open')) {\n        // Close it\n        $wrapper.removeClass('open');\n        $wrapper.slideUp('fast'); // Also clear the search.\n\n        clearSearch();\n      } else {\n        // Open it\n        $('.customize-panel-description').removeClass('open');\n        $('.customize-panel-description').slideUp('fast');\n        $wrapper.addClass('open');\n        $wrapper.slideDown('fast');\n        $(searchInputSelector).focus();\n      }\n    };\n\n    var expandSection = function expandSection(event) {\n      var sectionName = this.getAttribute('data-section');\n      var section = api.section(sectionName);\n      customizePanelsParent.removeClass('search-found');\n      document.getElementById('customify-search-results').innerHTML = '';\n      $(searchInputSelector).focus();\n      section.expand();\n    };\n    /**\n     * Clear Search input and display all the options.\n     */\n\n\n    var clearSearch = function clearSearch() {\n      customizePanelsParent.removeClass('search-found');\n      document.getElementById('customify-search-results').innerHTML = '';\n      document.getElementById('customify-customizer-search-input').value = '';\n      $(searchInputSelector).focus();\n    }; // When the customizer is ready prepare the search logic.\n\n\n    api.bind('ready', init);\n    return {\n      init: init\n    };\n  }());\n})(jQuery, customify, wp);\n\n//# sourceURL=webpack://sm.%5Bname%5D/./src/_js/customizer-search/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/_js/customizer-search/index.js"]();
/******/ 	(this.sm = this.sm || {}).customizerSearch = __webpack_exports__;
/******/ 	
/******/ })()
;