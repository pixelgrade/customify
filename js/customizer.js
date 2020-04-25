/** @namespace customify */
window.customify = window.customify || parent.customify || {};

(function ($, customify, wp) {
  const api = wp.customize
  const $window = $(window)
  const $document = $(document)
  let timeout = null

  // when the customizer is ready prepare our fields events
  api.bind('ready', function () {

    // Create a stack of callbacks bound to parent settings to be able to unbind them
    // when altering the connected_fields attribute.
    if (typeof customify.connectedFieldsCallbacks === 'undefined') {
      customify.connectedFieldsCallbacks = {}
    }

    // Initialize ACE editors.
    handleAceEditors()

    // Initialize simple select2 fields.
    $('.customify_select2').select2()

    // Initialize font fields.
    customify.fontFields.init()

    // For each range input add a value preview output.
    $('.accordion-section-content[id*="' + customify.config.options_name + '"], #sub-accordion-section-style_manager_section').each(function () {
      // Initialize range fields logic
      handleRangeFields(this)
    })

    // Handle presets (legacy).
    handlePresets()

    // Initialize custom background fields.
    customifyBackgroundJsControl.init()

    setTimeout(function () {
      customifyFoldingFields()
    }, 1000)

    // Handle reset buttons
    handleResetButtons()

    // Handle the section tabs (ex: Layout | Fonts | Colors)
    handleSectionTabs()

    // Bind any connected fields, except those in the Style Manager.
    // Those are handled by the appropriate Style Manager component (Color Palettes, Font Palettes, etc ).
    bindConnectedFields()

    // Handle the preview iframe.
    handlePreviewIframe()

    // Sometimes a php save may be needed. Trigger it if the appropiate URL var is present.
    if (getUrlVar('save_customizer_once')) {
      api.previewer.save()
    }
  })

  /**
   * Handle the ACE editor fields.
   */
  function handleAceEditors () {
    $('.customify_ace_editor').each(function (key, el) {
      const id = $(this).attr('id'),
        cssEditorInstance = ace.edit(id)

      const editor_type = $(this).data('editor_type')
      // init the ace editor
      cssEditorInstance.setTheme('ace/theme/github')
      cssEditorInstance.getSession().setMode('ace/mode/' + editor_type)

      // hide the textarea and enable the ace editor
      const textarea = $('#' + id + '_textarea').hide()
      cssEditorInstance.getSession().setValue(textarea.val())

      // each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
      // if the user types faster than this delay then reset it
      cssEditorInstance.getSession().on('change', function (event) {
        if (timeout !== null) {
          clearTimeout(timeout)
          timeout = null
        } else {
          timeout = setTimeout(function () {
            textarea.val(cssEditorInstance.getSession().getValue())
            textarea.trigger('change', ['customify'])
          }, 1500)
        }
      })
    })
  }

  /**
   * Handle the presets (legacy).
   */
  function handlePresets () {
    $('body').on('customify:preset-change', function (event) {
      const data = $(event.target).data('options')

      if (!_.isUndefined(data)) {
        $.each(data, function (settingID, value) {
          apiSetSettingValue(settingID, value)
        })
      }
    })
    $document.on('change', 'select.js-customify-preset', function () {
      const $source = $(this)
      const $target = $source.children('[value="' + $source.val() + '"]')
      $target.trigger('customify:preset-change')
    })
    $document.on('click', '.js-customify-preset input', function () {
      $(this).trigger('customify:preset-change')
    })
  }

  const handleRangeFields = function (el) {

    // For each range input add a number field (for preview mainly - but it can also be used for input)
    $(el).find('input[type="range"]').each(function () {
      const $range = $(this)
      let $number = $range.siblings('.range-value')

      if (!$number.length) {
        $number = $range.clone()

        $number
          .attr('type', 'number')
          .attr('class', 'range-value')
          .removeAttr('data-value_entry')

        if ($range.first().attr('id')) {
          $number.attr('id', $range.first().attr('id') + '_number')
        }
        $number.insertAfter($range)
      }

      function hasValidValue ($input) {
        const min = $input.attr('min')
        const max = $input.attr('max')
        const value = $input.val()

        if (typeof min !== 'undefined' && parseFloat(min) > parseFloat(value)) {
          return false
        }

        return !(typeof max !== 'undefined' && parseFloat(max) < parseFloat(value))
      }

      // Put the value into the number field.
      $range.on('input change', function (event) {
        if (event.target.value === $number.val()) {
          // Nothing to do if the values are identical.
          return;
        }

        $number.val($range.val())
      })

      // When clicking outside the number field or on Enter.
      $number.on('blur keyup', function (event) {
        if ('keyup' === event.type && event.keyCode !== 13) {
          return
        }

        if (event.target.value === $range.val()) {
          // Nothing to do if the values are identical.
          return;
        }

        if (!hasValidValue($number)) {
          $number.val($range.val())
          shake($number)
        } else {
          // Do not mark this trigger as being programmatically triggered by Customify since it is a result of a user input.
          $range.val($number.val()).trigger('change')
        }
      })

      function shake ($field) {
        $field.addClass('input-shake input-error')
        $field.one('animationend', function () {
          $field.removeClass('input-shake input-error')
        })
      }
    })
  }

  /**
   * Handle reset buttons in the Customizer.
   */
  function handleResetButtons () {
    const showResetButtons = $('button[data-action="reset_customify"]').length > 0

    if (showResetButtons) {
      createResetPanelButtons()
      createResetSectionButtons()

      $document.on('click', '.js-reset-panel', onResetPanel)
      $document.on('click', '.js-reset-section', onResetSection)
      $document.on('click', '#customize-control-reset_customify button', onReset)
    }
  }

  function createResetPanelButtons () {

    $('.panel-meta').each(function (i, obj) {
      const $this = $(obj)
      const container = $this.parents('.control-panel')
      let id = container.attr('id')

      if (typeof id !== 'undefined') {
        id = id.replace('sub-accordion-panel-', '')
        id = id.replace('accordion-panel-', '')
        const $buttonWrapper = $('<li class="customize-control customize-control-reset"></li>')
        const $button = $('<button class="button js-reset-panel" data-panel="' + id + '"></button>')

        $button.text(customify.l10n.panelResetButton).appendTo($buttonWrapper)
        $this.parent().append($buttonWrapper)
      }
    })
  }

  function createResetSectionButtons () {
    $('.accordion-section-content').each(function (el, key) {
      const $this = $(this)
      const sectionID = $this.attr('id')

      if (_.isUndefined(sectionID) || sectionID.indexOf(customify.config.options_name) === -1) {
        return
      }

      const id = sectionID.replace('sub-accordion-section-', '')
      const $button = $('<button class="button js-reset-section" data-section="' + id + '"></button>')
      const $buttonWrapper = $('<li class="customize-control customize-control-reset"></li>')

      $button.text(customify.l10n.sectionResetButton)
      $buttonWrapper.append($button)

      $this.append($buttonWrapper)
    })
  }

  function onReset (ev) {
    ev.preventDefault()

    const iAgree = confirm(customify.l10n.resetGlobalConfirmMessage)

    if (!iAgree) {
      return
    }

    $.each(api.settings.controls, function (key, ctrl) {
      const settingID = key.replace('_control', '')
      const setting = customify.config.settings[settingID]

      if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
        apiSetSettingValue(settingID, setting.default)
      }
    })

    api.previewer.save()
  }

  function onResetPanel (e) {
    e.preventDefault()

    const panelID = $(this).data('panel'),
      panel = api.panel(panelID),
      sections = panel.sections(),
      iAgree = confirm(customify.l10n.resetPanelConfirmMessage)

    if (!iAgree) {
      return
    }
    if (sections.length > 0) {
      $.each(sections, function () {
        const controls = this.controls()

        if (controls.length > 0) {
          $.each(controls, function (key, ctrl) {
            const settingID = ctrl.id.replace('_control', ''),
              setting = customify.config.settings[settingID]

            if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
              apiSetSettingValue(settingID, setting.default)
            }
          })
        }
      })
    }
  }

  function onResetSection (e) {
    e.preventDefault()

    const sectionID = $(this).data('section'),
      section = api.section(sectionID),
      controls = section.controls()

    const iAgree = confirm(customify.l10n.resetSectionConfirmMessage)

    if (!iAgree) {
      return
    }

    if (controls.length > 0) {
      $.each(controls, function (key, ctrl) {
        const setting_id = ctrl.id.replace('_control', ''),
          setting = customify.config.settings[setting_id]

        if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
          apiSetSettingValue(setting_id, setting.default)
        }
      })
    }
  }

  function handleSectionTabs () {
    const $navs = $('.js-section-navigation')

    $navs.each(function () {
      const $nav = $(this)
      const $title = $nav.parents('.accordion-section-content').find('.customize-section-title')
      const $parent = $nav.closest('.customize-control')

      $nav.appendTo($title)
      $title.parent().addClass('has-nav')
      $parent.addClass('screen-reader-text')
    })

    $('.js-section-navigation a').on('click', function (e) {
      e.preventDefault()

      const $this = $(this)
      const $sidebar = $this.parents('.wp-full-overlay-sidebar-content')
      const $parent = $this.parents('.accordion-section-content')
      const href = $this.attr('href')

      if (href !== '#') {
        const actionsHeight = $('#customize-header-actions').outerHeight()
        const titleHeight = $parent.find('.customize-section-title').outerHeight()
        const $target = $(href)
        const offset = $target.position().top

        $sidebar.animate({scrollTop: offset - titleHeight - actionsHeight}, 500)
      }
    })
  }

  const getConnectedFieldsCallback = function (parentSettingData, parentSettingID) {
    return function (newValue, oldValue) {
      _.each(parentSettingData.connected_fields, function (connectedFieldData) {
        if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
          return
        }
        const setting = api(connectedFieldData.setting_id)
        if (_.isUndefined(setting)) {
          return
        }
        setting.set(newValue)
      })
    }
  }

  const bindConnectedFields = function () {
    _.each(api.settings.settings, function (parentSettingData, parentSettingID) {
      // We don't want to handle the binding of the Style Manager settings
      if (typeof customify.colorPalettes !== 'undefined'
        && typeof customify.colorPalettes.masterSettingIds !== 'undefined'
        && _.includes(customify.colorPalettes.masterSettingIds, parentSettingID)) {
        return
      }
      if (typeof customify.fontPalettes !== 'undefined'
        && typeof customify.fontPalettes.masterSettingIds !== 'undefined'
        && _.includes(customify.fontPalettes.masterSettingIds, parentSettingID)) {
        return
      }

      const parent_setting = api(parentSettingID)
      if (typeof parentSettingData.connected_fields !== 'undefined') {
        customify.connectedFieldsCallbacks[parentSettingID] = getConnectedFieldsCallback(parentSettingData, parentSettingID)
        parent_setting.bind(customify.connectedFieldsCallbacks[parentSettingID])
      }
    })
  }

  /**
   * This function will search for all the interdependend fields and make a bound between them.
   * So whenever a target is changed, it will take actions to the dependent fields.
   * @TODO  this is still written in a barbaric way, refactor when needed
   */
  const customifyFoldingFields = function () {

    if (_.isUndefined(customify.config) || _.isUndefined(customify.config.settings)) {
      return // bail
    }

    $.fn.reactor.defaults.compliant = function () {
      $(this).slideDown()
      $(this).find(':disabled').attr({disabled: false})
    }

    $.fn.reactor.defaults.uncompliant = function () {
      $(this).slideUp()
      $(this).find(':enabled').attr({disabled: true})
    }

    let IS = $.extend({}, $.fn.reactor.helpers)

    const bindFoldingEvents = function (parentID, field, relation) {

      let key = null

      if (_.isString(field)) {
        key = field
      } else if (!_.isUndefined(field.id)) {
        key = field.id
      } else if (_.isString(field[0])) {
        key = field[0]
      } else {
        return // no key, no fun
      }

      let value = 1, // by default we use 1 the most used value for checkboxes or inputs
        between = [0, 1] // can only be `show` or `hide`

      const target_key = customify.config.options_name + '[' + key + ']'
      const target_type = customify.config.settings[target_key].type

      // we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
      // but we also support a non-associative array like `array( $id, $value, $compare )`
      if (!_.isUndefined(field.value)) {
        value = field.value
      } else if (!_.isUndefined(field[1]) && !_.isString(field[1])) {
        value = field[1]
      }

      if (!_.isUndefined(field.between)) {
        between = field.between
      }

      /**
       * Now for each target we have, we will bind a change event to hide or show the dependent fields
       */
      const target_selector = '[data-customize-setting-link="' + customify.config.options_name + '[' + key + ']"]'

      switch (target_type) {
        case 'checkbox':
          $(parentID).reactIf(target_selector, function () {
            return $(this).is(':checked') == value
          })
          break

        case 'radio':
        case 'sm_radio':
        case 'sm_switch':
        case 'radio_image':
        case 'radio_html':

          // in case of an array of values we use the ( val in array) condition
          if (_.isObject(value)) {
            value = _.toArray(value)
            $(parentID).reactIf(target_selector, function () {
              return (
                value.indexOf($(target_selector + ':checked').val()) !== -1
              )
            })
          } else { // in any other case we use a simple == comparison
            $(parentID).reactIf(target_selector, function () {
              return $(target_selector + ':checked').val() == value
            })
          }
          break

        case 'range':
          const x = IS.Between(between[0], between[1])

          $(parentID).reactIf(target_selector, x)
          break

        default:
          // in case of an array of values we use the ( val in array) condition
          if (_.isObject(value)) {
            value = _.toArray(value)
            $(parentID).reactIf(target_selector, function () {
              return (
                value.indexOf($(target_selector).val()) !== -1
              )
            })
          } else { // in any other case we use a simple == comparison
            $(parentID).reactIf(target_selector, function () {
              return $(target_selector).val() == value
            })
          }
          break
      }

      $(target_selector).trigger('change', ['customify'])
      $('.reactor').trigger('change.reactor') // triggers all events on load
    }

    $.each(customify.config.settings, function (id, field) {
      /**
       * Here we have the id of the fields. but we know for sure that we just need his parent selector
       * So we just create it
       */
      let parentID = id.replace('[', '-')
      parentID = parentID.replace(']', '')
      parentID = '#customize-control-' + parentID + '_control'

      // get only the fields that have a 'show_if' property
      if (field.hasOwnProperty('show_if')) {
        let relation = 'AND'

        if (!_.isUndefined(field.show_if.relation)) {
          relation = field.show_if.relation
          // remove the relation property, we need the config to be array based only
          delete field.show_if.relation
        }

        /**
         * The 'show_if' can be a simple array with one target like: [ id, value, comparison, action ]
         * Or it could be an array of multiple targets and we need to process both cases
         */

        if (!_.isUndefined(field.show_if.id)) {
          bindFoldingEvents(parentID, field.show_if, relation)
        } else if (_.isObject(field.show_if)) {
          $.each(field.show_if, function (i, j) {
            bindFoldingEvents(parentID, j, relation)
          })
        }
      }
    })
  }

  /**
   * Set a setting value.
   *
   * Mostly used for resetting settings (via the reset buttons) but also for the preset (legacy) field.
   *
   * @param settingID
   * @param value
   */
  const apiSetSettingValue = function (settingID, value) {
    const setting = api(settingID),
      field = $('[data-customize-setting-link="' + settingID + '"]'),
      fieldClass = $(field).parent().attr('class')

    if (!_.isUndefined(fieldClass) && fieldClass === 'font-options__wrapper') {

      // if the value is a simple string it must be the font family
      if (_.isString(value)) {
        setting.set({'font_family': value})
      } else if (_.isObject(value)) {
        const standardValue = {}
        // We will process each font property and update it
        _.each(value, function (val, key) {
          // We need to map the keys to the data attributes we are using - I know :(
          let mappedKey = key
          switch (key) {
            case 'font-family':
              mappedKey = 'font_family'
              break
            case 'font-size':
              mappedKey = 'font_size'
              break
            case 'font-weight':
              mappedKey = 'font_variant'
              break
            case 'letter-spacing':
              mappedKey = 'letter_spacing'
              break
            case 'text-transform':
              mappedKey = 'text_transform'
              break
            default:
              break
          }

          standardValue[mappedKey] = val
        })

        setting.set(standardValue)
      }
    } else {
      setting.set(value)
    }
  }

  const handlePreviewIframe = function () {
    api.previewer.bind('synced', function () {
      scaleIframe()

      api.previewedDevice.bind(scaleIframe)
      $window.on('resize', scaleIframe)
    })

    $('.collapse-sidebar').on('click', function () {
      setTimeout(scaleIframe, 300)
    })
  }

  const scaleIframe = function () {
    const $previewIframe = $('.wp-full-overlay')

    // remove CSS properties that may have been previously added
    $previewIframe.find('iframe').css({
      width: '',
      height: '',
      transformOrigin: '',
      transform: ''
    })

    // scaling of the site preview should be done only in desktop preview mode
    if (api.previewedDevice.get() !== 'desktop') {
      return
    }

    const iframeWidth = $previewIframe.width()
    const windowWidth = $window.width()
    const windowHeight = $window.height()

    // get the ratio between the site preview and actual browser width
    const scale = windowWidth / iframeWidth

    // for an accurate preview at resolutions where media queries may intervene
    // increase the width of the iframe and use CSS transforms to scale it back down
    if (iframeWidth > 720 && iframeWidth < 1100) {
      $previewIframe.find('iframe').css({
        width: iframeWidth * scale,
        height: windowHeight * scale,
        transformOrigin: 'left top',
        transform: 'scale(' + 1 / scale + ')'
      })
    }
  }

  /** Modules **/

  const customifyBackgroundJsControl = (
    function () {
      'use strict'

      function init () {
        // Upload media button
        $('.customize-control-custom_background .background_upload_button').unbind().on('click', function (event) {
          addImage(event, $(this).parents('.customize-control-custom_background:first'))
        })

        // Remove the image button
        $('.customize-control-custom_background .remove-image, .customize-control-custom_background .remove-file').unbind('click').on('click', function (e) {
          removeImage($(this).parents('.customize-control-custom_background:first'))
        })
      }

      // Add a file via the wp.media function
      function addImage (event, selector) {
        // Stop this from propagating.
        event.preventDefault()

        let frame
        const $thisElement = $(this)

        // If the media frame already exists, reopen it.
        if (frame) {
          frame.open()
          return
        }

        // Create the media frame.
        frame = wp.media({
          multiple: false,
          library: {
            //type: 'image' //Only allow images
          },
          // Set the title of the modal.
          title: $thisElement.data('choose'),

          // Customize the submit button.
          button: {
            // Set the text of the button.
            text: $thisElement.data('update')
            // Tell the button not to close the modal, since we're
            // going to refresh the page when the image is selected.
          }
        })

        // When an image is selected, run a callback.
        frame.on('select', function () {
          // Grab the selected attachment.
          const attachment = frame.state().get('selection').first()
          frame.close()

          if (attachment.attributes.type !== 'image') {
            return
          }

          selector.find('.customify_background_input.background-image').val(attachment.attributes.url)

          selector.find('.upload').attr('value', attachment.attributes.url)
          selector.find('.upload-id').attr('value', attachment.attributes.id)
          selector.find('.upload-height').attr('value', attachment.attributes.height)
          selector.find('.upload-width').attr('value', attachment.attributes.width)

          let thumbSrc = attachment.attributes.url
          if (!_.isUndefined(attachment.attributes.sizes) && !_.isUndefined(attachment.attributes.sizes.thumbnail)) {
            thumbSrc = attachment.attributes.sizes.thumbnail.url
          } else if (!_.isUndefined(attachment.attributes.sizes)) {
            let height = attachment.attributes.height
            for (let key in attachment.attributes.sizes) {
              const object = attachment.attributes.sizes[key]
              if (object.height < height) {
                height = object.height
                thumbSrc = object.url
              }
            }
          } else {
            thumbSrc = attachment.attributes.icon
          }

          if (!selector.find('.upload').hasClass('noPreview')) {
            selector.find('.preview_screenshot').empty().hide().append('<img class="preview_image" src="' + thumbSrc + '">').slideDown('fast')
          }
          selector.find('.remove-image').removeClass('hide') // Show "Remove" button
          selector.find('.customify_background_select').removeClass('hide') // Show background selects

          updateData(selector)
        })

        // Finally, open the modal.
        frame.open()
      }

      // Update the background data
      function updateData (selector) {

        let $parent = selector.parents('.customize-control-custom_background:first')

        if (selector.hasClass('customize-control-custom_background')) {
          $parent = selector
        }

        if ($parent.length > 0) {
          $parent = $($parent[0])
        } else {
          return
        }

        const settingID = $parent.find('.button.background_upload_button').data('setting_id'),
          setting = api.instance(settingID)

        const background_data = {}

        $parent.find('.customify_background_select, .customify_background_input').each(function () {
          let data = $(this).serializeArray()[0]
          if (data && data.name.indexOf('[background-') !== -1) {
            background_data[$(this).data('select_name')] = data.value
          }
        })

        background_data.media = {}
        background_data.media.id = $parent.find('.upload-id').val()
        background_data.media.height = $parent.find('.upload-height').val()
        background_data.media.width = $parent.find('.upload-width').val()
        background_data.media.thumbnail = $parent.find('.upload-thumbnail').val()

        setting.set(background_data)
      }

      // Update the background preview
      function removeImage (parent) {
        const selector = parent.find('.upload_button_div')
        // This shouldn't have been run...
        if (!selector.find('.remove-image').addClass('hide')) {
          return
        }

        // Hide "Remove" button.
        selector.find('.remove-image').addClass('hide')
        parent.find('.customify_background_select').addClass('hide')

        parent.find('.upload').val(null)
        parent.find('.upload-id').val(null)
        parent.find('.upload-height').val(null)
        parent.find('.upload-width').val(null)
        parent.find('.customify_background_input.background-image').val(null)

        // Hide the screenshot
        parent.find('.preview_screenshot').slideUp()

        updateData(parent)
      }

      return {
        init: init
      }
    }
  )(jQuery)

  /** HELPERS **/

  const getUrlVar = function (name) {
    const vars = []
    let hash
    const hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&')

    for (let i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=')

      vars.push(hash[0])
      vars[hash[0]] = hash[1]
    }

    if (!_.isUndefined(vars[name])) {
      return vars[name]
    }
    return false
  }
})(jQuery, customify, wp)
