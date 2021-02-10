/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
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
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/

;// CONCATENATED MODULE: external "jQuery"
var external_jQuery_namespaceObject = jQuery;
var external_jQuery_default = /*#__PURE__*/__webpack_require__.n(external_jQuery_namespaceObject);
;// CONCATENATED MODULE: ./src/js/customizer/fields/color-select/index.js

var handleColorSelectFields = function handleColorSelectFields() {
  external_jQuery_default()('.js-color-select').each(function (i, obj) {
    convertToColorSelect(obj);
  });
};
var convertToColorSelect = function convertToColorSelect(element) {
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
;// CONCATENATED MODULE: ./src/js/customizer/fields/range/index.js

var handleRangeFields = function handleRangeFields() {
  var rangeControlSelector = ".accordion-section-content[id*=\"".concat(customify.config.options_name, "\"], #sub-accordion-section-sm_color_palettes_section");
  external_jQuery_default()(rangeControlSelector).each(function (i, container) {
    var $rangeFields = external_jQuery_default()(container).find('input[type="range"]'); // For each range input add a number field (for preview mainly - but it can also be used for input)

    $rangeFields.each(function (i, obj) {
      var $range = external_jQuery_default()(obj);
      var $number = $range.siblings('.range-value');

      if (!$number.length) {
        $number = $range.clone();
        $number.attr('type', 'text').attr('class', 'range-value').removeAttr('data-value_entry');
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
;// CONCATENATED MODULE: ./src/js/customizer/folding-fields.js

/**
 * This function will search for all the interdependend fields and make a bound between them.
 * So whenever a target is changed, it will take actions to the dependent fields.
 * @TODO  this is still written in a barbaric way, refactor when needed
 */

var handleFoldingFields = function handleFoldingFields() {
  if (_.isUndefined(customify.config) || _.isUndefined(customify.config.settings)) {
    return; // bail
  }

  (external_jQuery_default()).fn.reactor.defaults.compliant = function () {
    external_jQuery_default()(this).slideDown();
    external_jQuery_default()(this).find(':disabled').attr({
      disabled: false
    });
  };

  (external_jQuery_default()).fn.reactor.defaults.uncompliant = function () {
    external_jQuery_default()(this).slideUp();
    external_jQuery_default()(this).find(':enabled').attr({
      disabled: true
    });
  };

  var IS = external_jQuery_default().extend({}, (external_jQuery_default()).fn.reactor.helpers);

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

  external_jQuery_default().each(customify.config.settings, function (id, field) {
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
        external_jQuery_default().each(field.show_if, function (i, j) {
          bindFoldingEvents(parentID, j, relation);
        });
      }
    }
  });
};
;// CONCATENATED MODULE: ./src/js/customizer/scale-preview.js

var scalePreview = function scalePreview() {
  wp.customize.previewer.bind('synced', function () {
    scalePreviewIframe();
    wp.customize.previewedDevice.bind(scalePreviewIframe);
    external_jQuery_default()(window).on('resize', scalePreviewIframe);
  });
  external_jQuery_default()('.collapse-sidebar').on('click', function () {
    setTimeout(scalePreviewIframe, 300);
  });
};

var scalePreviewIframe = function scalePreviewIframe() {
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
;// CONCATENATED MODULE: ./src/js/customizer/reset.js


var createResetButtons = function createResetButtons() {
  var $document = $(document);
  var showResetButtons = $('button[data-action="reset_customify"]').length > 0;

  if (showResetButtons) {
    createResetPanelButtons();
    createResetSectionButtons();
    $document.on('click', '.js-reset-panel', onResetPanel);
    $document.on('click', '.js-reset-section', onResetSection);
    $document.on('click', '#customize-control-reset_customify button', onReset);
  }
};

function createResetPanelButtons() {
  $('.panel-meta').each(function (i, obj) {
    var $this = $(obj);
    var container = $this.parents('.control-panel');
    var id = container.attr('id');

    if (typeof id !== 'undefined') {
      id = id.replace('sub-accordion-panel-', '');
      id = id.replace('accordion-panel-', '');
      var $buttonWrapper = $('<li class="customize-control customize-control-reset"></li>');
      var $button = $('<button class="button js-reset-panel" data-panel="' + id + '"></button>');
      $button.text(customify.l10n.panelResetButton).appendTo($buttonWrapper);
      $this.parent().append($buttonWrapper);
    }
  });
}

function createResetSectionButtons() {
  $('.accordion-section-content').each(function (el, key) {
    var $this = $(this);
    var sectionID = $this.attr('id');

    if (_.isUndefined(sectionID) || sectionID.indexOf(customify.config.options_name) === -1) {
      return;
    }

    var id = sectionID.replace('sub-accordion-section-', '');
    var $button = $('<button class="button js-reset-section" data-section="' + id + '"></button>');
    var $buttonWrapper = $('<li class="customize-control customize-control-reset"></li>');
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

  $.each(api.settings.controls, function (key, ctrl) {
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
  var panelID = $(this).data('panel'),
      panel = api.panel(panelID),
      sections = panel.sections(),
      iAgree = confirm(customify.l10n.resetPanelConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (sections.length > 0) {
    $.each(sections, function () {
      var controls = this.controls();

      if (controls.length > 0) {
        $.each(controls, function (key, ctrl) {
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
  var sectionID = $(this).data('section'),
      section = api.section(sectionID),
      controls = section.controls();
  var iAgree = confirm(customify.l10n.resetSectionConfirmMessage);

  if (!iAgree) {
    return;
  }

  if (controls.length > 0) {
    $.each(controls, function (key, ctrl) {
      var setting_id = ctrl.id.replace('_control', ''),
          setting = customify.config.settings[setting_id];

      if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
        apiSetSettingValue(setting_id, setting.default);
      }
    });
  }
}
;// CONCATENATED MODULE: ./src/js/customizer/index.js






wp.customize.bind('ready', function () {
  handleRangeFields();
  handleColorSelectFields(); // @todo check reason for this timeout

  setTimeout(function () {
    handleFoldingFields();
  }, 1000); // Initialize simple select2 fields.

  external_jQuery_default()('.customify_select2').select2(); // Initialize font fields.

  customify.fontFields.init();
  scalePreview();
});
/******/ })()
;