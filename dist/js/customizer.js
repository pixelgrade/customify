/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 7);
/******/ })
/************************************************************************/
/******/ ({

/***/ 0:
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),

/***/ 7:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external "jQuery"
var external_jQuery_ = __webpack_require__(0);
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_);

// CONCATENATED MODULE: ./src/js/customizer/fields/color-select/index.js

var color_select_handleColorSelectFields = function handleColorSelectFields() {
  external_jQuery_default()('.js-color-select').each(function (i, obj) {
    color_select_convertToColorSelect(obj);
  });
};
var color_select_convertToColorSelect = function convertToColorSelect(element) {
  var $select = external_jQuery_default()(element);
  var $selectOptions = $select.find('option');
  var $colorSelect = external_jQuery_default()('<div class="customify-color-select">');
  var $optionsList = external_jQuery_default()('<div class="customify-color-select__option-list">');
  $selectOptions.each(function (i, option) {
    var $option = external_jQuery_default()(option);
    var label = $option.text();
    var value = $option.attr('value');
    var $colorSelectOptionLabel = external_jQuery_default()('<div class="customify-color-select__option-label">');
    var $colorSelectOption = external_jQuery_default()('<div class="customify-color-select__option">');
    $colorSelectOptionLabel.text(label).appendTo($colorSelectOption);
    $colorSelectOption.data('value', value).appendTo($optionsList);
    $colorSelectOption.addClass('customify-color-select__option--' + value);
  });
  $optionsList.appendTo($colorSelect);
  var $colorSelectOptions = $colorSelect.find('.customify-color-select__option');
  $colorSelectOptions.each(function (i, option) {
    var $colorSelectOption = external_jQuery_default()(option);
    var value = $colorSelectOption.data('value');
    $colorSelectOption.on('click', function () {
      $select.val(value).change();
    });
  });
  $colorSelect.insertBefore($select);
  $select.hide();

  function updateColorSelect() {
    var value = $select.val();
    var $colorSelectOption = $colorSelectOptions.filter(function (index, obj) {
      return external_jQuery_default()(obj).data('value') === value;
    });

    if ($colorSelectOption.length) {
      $colorSelectOptions.removeClass('customify-color-select__option--selected');
      $colorSelectOption.addClass('customify-color-select__option--selected');
    }
  }

  updateColorSelect();
  $select.on('change', updateColorSelect);
};
// CONCATENATED MODULE: ./src/js/customizer/fields/ace-editor/index.js

var ace_editor_handleAceEditors = function handleAceEditors() {
  external_jQuery_default()('.customify_ace_editor').each(function (key, el) {
    var id = external_jQuery_default()(this).attr('id'),
        cssEditorInstance = ace.edit(id);
    var editor_type = external_jQuery_default()(this).data('editor_type'); // init the ace editor

    cssEditorInstance.setTheme('ace/theme/github');
    cssEditorInstance.getSession().setMode('ace/mode/' + editor_type); // hide the textarea and enable the ace editor

    var textarea = external_jQuery_default()('#' + id + '_textarea').hide();
    cssEditorInstance.getSession().setValue(textarea.val()); // each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
    // if the user types faster than this delay then reset it

    cssEditorInstance.getSession().on('change', function (event) {
      if (timeout !== null) {
        clearTimeout(timeout);
        timeout = null;
      } else {
        timeout = setTimeout(function () {
          textarea.val(cssEditorInstance.getSession().getValue());
          textarea.trigger('change', ['customify']);
        }, 1500);
      }
    });
  });
};
// CONCATENATED MODULE: ./src/js/customizer/fields/range/index.js

var range_handleRangeFields = function handleRangeFields() {
  var rangeControlSelector = ".accordion-section-content[id*=\"".concat(customify.config.options_name, "\"], #sub-accordion-section-style_manager_section");
  external_jQuery_default()(rangeControlSelector).each(function (i, container) {
    var $rangeFields = external_jQuery_default()(container).find('input[type="range"]'); // For each range input add a number field (for preview mainly - but it can also be used for input)

    $rangeFields.each(function (i, obj) {
      var $range = external_jQuery_default()(obj);
      var $number = $range.siblings('.range-value');

      if (!$number.length) {
        $number = $range.clone();
        $number.attr('type', 'number').attr('class', 'range-value').removeAttr('data-value_entry');
        $number.data('source', $range);

        if ($range.first().attr('id')) {
          $number.attr('id', $range.first().attr('id') + '_number');
        }

        $number.insertAfter($range);
      } // Put the value into the number field.


      $range.on('input change', function (event) {
        if (event.target.value === $number.val()) {
          // Nothing to do if the values are identical.
          return;
        }

        $number.val(event.target.value);
      }); // When clicking outside the number field or on Enter.

      $number.on('blur keyup', onRangePreviewBlur);
    });
  });
};

function onRangePreviewBlur(event) {
  var $number = external_jQuery_default()(event.target);
  var $range = $number.data('source');

  if ('keyup' === event.type && event.keyCode !== 13) {
    return;
  }

  if (event.target.value === $range.val()) {
    // Nothing to do if the values are identical.
    return;
  }

  if (!hasValidValue($number)) {
    $number.val($range.val());
    shake($number);
  } else {
    // Do not mark this trigger as being programmatically triggered by Customify since it is a result of a user input.
    $range.val($number.val()).trigger('change');
  }
}

function hasValidValue($input) {
  var min = $input.attr('min');
  var max = $input.attr('max');
  var value = $input.val();

  if (typeof min !== 'undefined' && parseFloat(min) > parseFloat(value)) {
    return false;
  }

  if (typeof max !== 'undefined' && parseFloat(max) < parseFloat(value)) {
    return false;
  }

  return true;
}

function shake($field) {
  $field.addClass('input-shake input-error');
  $field.one('animationend', function () {
    $field.removeClass('input-shake input-error');
  });
}
// CONCATENATED MODULE: ./src/js/customizer/folding-fields.js

/**
 * This function will search for all the interdependend fields and make a bound between them.
 * So whenever a target is changed, it will take actions to the dependent fields.
 * @TODO  this is still written in a barbaric way, refactor when needed
 */

var folding_fields_handleFoldingFields = function handleFoldingFields() {
  if (_.isUndefined(customify.config) || _.isUndefined(customify.config.settings)) {
    return; // bail
  }

  external_jQuery_default.a.fn.reactor.defaults.compliant = function () {
    external_jQuery_default()(this).slideDown();
    external_jQuery_default()(this).find(':disabled').attr({
      disabled: false
    });
  };

  external_jQuery_default.a.fn.reactor.defaults.uncompliant = function () {
    external_jQuery_default()(this).slideUp();
    external_jQuery_default()(this).find(':enabled').attr({
      disabled: true
    });
  };

  var IS = external_jQuery_default.a.extend({}, external_jQuery_default.a.fn.reactor.helpers);

  var bindFoldingEvents = function bindFoldingEvents(parentID, field, relation) {
    var key = null;

    if (_.isString(field)) {
      key = field;
    } else if (!_.isUndefined(field.id)) {
      key = field.id;
    } else if (_.isString(field[0])) {
      key = field[0];
    } else {
      return; // no key, no fun
    }

    var value = 1,
        // by default we use 1 the most used value for checkboxes or inputs
    between = [0, 1]; // can only be `show` or `hide`

    var target_key = customify.config.options_name + '[' + key + ']';
    var target_type = customify.config.settings[target_key].type; // we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
    // but we also support a non-associative array like `array( $id, $value, $compare )`

    if (!_.isUndefined(field.value)) {
      value = field.value;
    } else if (!_.isUndefined(field[1]) && !_.isString(field[1])) {
      value = field[1];
    }

    if (!_.isUndefined(field.between)) {
      between = field.between;
    }
    /**
     * Now for each target we have, we will bind a change event to hide or show the dependent fields
     */


    var target_selector = '[data-customize-setting-link="' + customify.config.options_name + '[' + key + ']"]';

    switch (target_type) {
      case 'checkbox':
        external_jQuery_default()(parentID).reactIf(target_selector, function () {
          return external_jQuery_default()(this).is(':checked') == value;
        });
        break;

      case 'radio':
      case 'sm_radio':
      case 'sm_switch':
      case 'radio_image':
      case 'radio_html':
        // in case of an array of values we use the ( val in array) condition
        if (_.isObject(value)) {
          value = _.toArray(value);
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return value.indexOf(external_jQuery_default()(target_selector + ':checked').val()) !== -1;
          });
        } else {
          // in any other case we use a simple == comparison
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return external_jQuery_default()(target_selector + ':checked').val() == value;
          });
        }

        break;

      case 'range':
        var x = IS.Between(between[0], between[1]);
        external_jQuery_default()(parentID).reactIf(target_selector, x);
        break;

      default:
        // in case of an array of values we use the ( val in array) condition
        if (_.isObject(value)) {
          value = _.toArray(value);
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return value.indexOf(external_jQuery_default()(target_selector).val()) !== -1;
          });
        } else {
          // in any other case we use a simple == comparison
          external_jQuery_default()(parentID).reactIf(target_selector, function () {
            return external_jQuery_default()(target_selector).val() == value;
          });
        }

        break;
    }

    external_jQuery_default()(target_selector).trigger('change', ['customify']);
    external_jQuery_default()('.reactor').trigger('change.reactor'); // triggers all events on load
  };

  external_jQuery_default.a.each(customify.config.settings, function (id, field) {
    /**
     * Here we have the id of the fields. but we know for sure that we just need his parent selector
     * So we just create it
     */
    var parentID = id.replace('[', '-');
    parentID = parentID.replace(']', '');
    parentID = '#customize-control-' + parentID + '_control'; // get only the fields that have a 'show_if' property

    if (field.hasOwnProperty('show_if')) {
      var relation = 'AND';

      if (!_.isUndefined(field.show_if.relation)) {
        relation = field.show_if.relation; // remove the relation property, we need the config to be array based only

        delete field.show_if.relation;
      }
      /**
       * The 'show_if' can be a simple array with one target like: [ id, value, comparison, action ]
       * Or it could be an array of multiple targets and we need to process both cases
       */


      if (!_.isUndefined(field.show_if.id)) {
        bindFoldingEvents(parentID, field.show_if, relation);
      } else if (_.isObject(field.show_if)) {
        external_jQuery_default.a.each(field.show_if, function (i, j) {
          bindFoldingEvents(parentID, j, relation);
        });
      }
    }
  });
};
// CONCATENATED MODULE: ./src/js/customizer/scale-preview.js

var scale_preview_scalePreview = function scalePreview() {
  wp.customize.previewer.bind('synced', function () {
    scale_preview_scalePreviewIframe();
    wp.customize.previewedDevice.bind(scale_preview_scalePreviewIframe);
    external_jQuery_default()(window).on('resize', scale_preview_scalePreviewIframe);
  });
  external_jQuery_default()('.collapse-sidebar').on('click', function () {
    setTimeout(scale_preview_scalePreviewIframe, 300);
  });
};

var scale_preview_scalePreviewIframe = function scalePreviewIframe() {
  var $window = external_jQuery_default()(window);
  var $previewIframe = external_jQuery_default()('.wp-full-overlay'); // remove CSS properties that may have been previously added

  $previewIframe.find('iframe').css({
    width: '',
    height: '',
    transformOrigin: '',
    transform: ''
  }); // scaling of the site preview should be done only in desktop preview mode

  if (wp.customize.previewedDevice.get() !== 'desktop') {
    return;
  }

  var iframeWidth = $previewIframe.width();
  var windowWidth = $window.width();
  var windowHeight = $window.height(); // get the ratio between the site preview and actual browser width

  var scale = windowWidth / iframeWidth; // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the iframe and use CSS transforms to scale it back down

  if (iframeWidth > 720 && iframeWidth < 1100) {
    $previewIframe.find('iframe').css({
      width: iframeWidth * scale,
      height: windowHeight * scale,
      transformOrigin: 'left top',
      transform: 'scale(' + 1 / scale + ')'
    });
  }
};
// CONCATENATED MODULE: ./src/js/customizer/utils.js
/**
 * Set a setting value.
 *
 * Mostly used for resetting settings (via the reset buttons) but also for the preset (legacy) field.
 *
 * @param settingID
 * @param value
 */
var apiSetSettingValue = function apiSetSettingValue(settingID, value) {
  var setting = api(settingID),
      field = $('[data-customize-setting-link="' + settingID + '"]'),
      fieldClass = $(field).parent().attr('class');

  if (!_.isUndefined(fieldClass) && fieldClass === 'font-options__wrapper') {
    // if the value is a simple string it must be the font family
    if (_.isString(value)) {
      setting.set({
        'font_family': value
      });
    } else if (_.isObject(value)) {
      var standardValue = {}; // We will process each font property and update it

      _.each(value, function (val, key) {
        // We need to map the keys to the data attributes we are using - I know :(
        var mappedKey = key;

        switch (key) {
          case 'font-family':
            mappedKey = 'font_family';
            break;

          case 'font-size':
            mappedKey = 'font_size';
            break;

          case 'font-weight':
            mappedKey = 'font_variant';
            break;

          case 'letter-spacing':
            mappedKey = 'letter_spacing';
            break;

          case 'text-transform':
            mappedKey = 'text_transform';
            break;

          default:
            break;
        }

        standardValue[mappedKey] = val;
      });

      setting.set(standardValue);
    }
  } else {
    setting.set(value);
  }
};
// CONCATENATED MODULE: ./src/js/customizer/reset.js


var reset_createResetButtons = function createResetButtons() {
  var $document = external_jQuery_default()(document);
  var showResetButtons = external_jQuery_default()('button[data-action="reset_customify"]').length > 0;

  if (showResetButtons) {
    createResetPanelButtons();
    createResetSectionButtons();
    $document.on('click', '.js-reset-panel', onResetPanel);
    $document.on('click', '.js-reset-section', onResetSection);
    $document.on('click', '#customize-control-reset_customify button', onReset);
  }
};

function createResetPanelButtons() {
  external_jQuery_default()('.panel-meta').each(function (i, obj) {
    var $this = external_jQuery_default()(obj);
    var container = $this.parents('.control-panel');
    var id = container.attr('id');

    if (typeof id !== 'undefined') {
      id = id.replace('sub-accordion-panel-', '');
      id = id.replace('accordion-panel-', '');
      var $buttonWrapper = external_jQuery_default()('<li class="customize-control customize-control-reset"></li>');
      var $button = external_jQuery_default()('<button class="button js-reset-panel" data-panel="' + id + '"></button>');
      $button.text(customify.l10n.panelResetButton).appendTo($buttonWrapper);
      $this.parent().append($buttonWrapper);
    }
  });
}

function createResetSectionButtons() {
  external_jQuery_default()('.accordion-section-content').each(function (el, key) {
    var $this = external_jQuery_default()(this);
    var sectionID = $this.attr('id');

    if (_.isUndefined(sectionID) || sectionID.indexOf(customify.config.options_name) === -1) {
      return;
    }

    var id = sectionID.replace('sub-accordion-section-', '');
    var $button = external_jQuery_default()('<button class="button js-reset-section" data-section="' + id + '"></button>');
    var $buttonWrapper = external_jQuery_default()('<li class="customize-control customize-control-reset"></li>');
    $button.text(customify.l10n.sectionResetButton);
    $buttonWrapper.append($button);
    $this.append($buttonWrapper);
  });
}

function onReset(ev) {
  ev.preventDefault();
  var iAgree = confirm(customify.l10n.resetGlobalConfirmMessage);

  if (!iAgree) {
    return;
  }

  external_jQuery_default.a.each(api.settings.controls, function (key, ctrl) {
    var settingID = key.replace('_control', '');
    var setting = customify.config.settings[settingID];

    if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
      apiSetSettingValue(settingID, setting.default);
    }
  });
  api.previewer.save();
}

function onResetPanel(e) {
  e.preventDefault();
  var panelID = external_jQuery_default()(this).data('panel'),
      panel = api.panel(panelID),
      sections = panel.sections(),
      iAgree = confirm(customify.l10n.resetPanelConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (sections.length > 0) {
    external_jQuery_default.a.each(sections, function () {
      var controls = this.controls();

      if (controls.length > 0) {
        external_jQuery_default.a.each(controls, function (key, ctrl) {
          var settingID = ctrl.id.replace('_control', ''),
              setting = customify.config.settings[settingID];

          if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
            apiSetSettingValue(settingID, setting.default);
          }
        });
      }
    });
  }
}

function onResetSection(e) {
  e.preventDefault();
  var sectionID = external_jQuery_default()(this).data('section'),
      section = api.section(sectionID),
      controls = section.controls();
  var iAgree = confirm(customify.l10n.resetSectionConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (controls.length > 0) {
    external_jQuery_default.a.each(controls, function (key, ctrl) {
      var setting_id = ctrl.id.replace('_control', ''),
          setting = customify.config.settings[setting_id];

      if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
        apiSetSettingValue(setting_id, setting.default);
      }
    });
  }
}
// CONCATENATED MODULE: ./src/js/customizer/index.js






wp.customize.bind('ready', function () {
  range_handleRangeFields();
  color_select_handleColorSelectFields();
  ace_editor_handleAceEditors(); // could be removed
  // @todo check reason for this timeout

  setTimeout(function () {
    folding_fields_handleFoldingFields();
  }, 1000);
  scale_preview_scalePreview();
});

/***/ })

/******/ });