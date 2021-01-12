import _ from 'lodash';
import $ from 'jquery';

import { filterColor, getCurrentPaletteColors } from './utils';

/** @namespace customify */
window.customify = window.customify || parent.customify || {};

const newMasterIDs = [
  'sm_accent_color_switch_master',
  'sm_accent_color_select_master',
  'sm_text_color_switch_master',
  'sm_text_color_select_master',
];

const switchColorSelector = '#_customize-input-sm_dark_color_switch_slider_control';
const selectColorSelector = '#_customize-input-sm_dark_color_select_slider_control';
const primaryColorSelector = '#_customize-input-sm_dark_color_primary_slider_control';
const secondaryColorSelector = '#_customize-input-sm_dark_color_secondary_slider_control';
const tertiaryColorSelector = '#_customize-input-sm_dark_color_tertiary_slider_control';

const colorSlidersSelector = [
  switchColorSelector,
  selectColorSelector,
  primaryColorSelector,
  secondaryColorSelector,
  tertiaryColorSelector
].join( ', ' );

const defaultVariation = 'light';

/**
 * Expose the API publicly on window.customify.colorPalettes
 *
 * @namespace customify.colorPalettes
 */
if ( typeof customify.colorPalettes === 'undefined' ) {
  customify.colorPalettes = {}
}

_.extend( customify.colorPalettes, function () {
  const api = wp.customize
  let apiSettings

  const filteredColors = {}
  const $colorSliders = $( colorSlidersSelector );

  let setupGlobalsDone = false

  const setupGlobals = () => {

    if (setupGlobalsDone) {
      return
    }

    // Initialize filtered colors global.
    _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
      filteredColors[settingID] = ''
    })

    // Cache initial settings configuration to be able to update connected fields on variation change.
    if (typeof customify.settingsClone === 'undefined') {
      customify.settingsClone = $.extend(true, {}, apiSettings)
    }

    // Create a stack of callbacks bound to parent settings to be able to unbind them
    // when altering the connected_fields attribute.
    if (typeof customify.colorPalettes.connectedFieldsCallbacks === 'undefined') {
      customify.colorPalettes.connectedFieldsCallbacks = {}
    }

    setupGlobalsDone = true
  }

  const resetSettings = () => {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each(settingIDs, function (settingID) {
      const setting = api(settingID)

      if (typeof setting !== 'undefined') {
        let parentSettingData = apiSettings[settingID]

        const finalValue = _.includes(newMasterIDs,settingID) ? setting() : getFilteredColor( settingID )

        _.each(parentSettingData.connected_fields, function (connectedFieldData) {
          if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
            return
          }
          const connectedSetting = api( connectedFieldData.setting_id )
          if ( _.isUndefined( connectedSetting ) ) {
            return
          }

          connectedSetting.set( finalValue )
        })

        // Also set the final setting value, for safe keeping.
        const finalSettingID = `${ settingID }_final`;
        const finalSetting = api( finalSettingID );
        if ( !_.isUndefined( finalSetting ) ) {
          if ( !_.isUndefined( parentSettingData.connected_fields ) && !_.isEmpty( parentSettingData.connected_fields ) ) {
            finalSetting.set( finalValue )
          } else {
            finalSetting.set( '' )
          }
        }
      }
    })
  }

  const updateFilteredColors = () => {
    _.each(customify.colorPalettes.masterSettingIds, function (settingID) {
      const setting = api(settingID)

      if (typeof setting !== 'undefined') {
        const value = setting()
        filteredColors[settingID] = filterColor(value)
      }
    })
  }

  const getFilteredColor = (settingID) => {
    return filteredColors[settingID]
  }

  const getMasterFieldCallback = function (parentSettingData, parentSettingID) {
    return function (newValue, oldValue) {
      const finalValue = _.includes(newMasterIDs,parentSettingID) ? newValue : getFilteredColor(parentSettingID)

      _.each(parentSettingData.connected_fields, function (connectedFieldData) {
        if (_.isUndefined(connectedFieldData) || _.isUndefined(connectedFieldData.setting_id) || !_.isString(connectedFieldData.setting_id)) {
          return
        }
        const setting = api(connectedFieldData.setting_id)
        if (_.isUndefined(setting)) {
          return
        }

        setting.set(finalValue)
      })
      updateFilterPreviews()

      // Also set the final setting value, for safe keeping.
      const finalSettingID = parentSettingID + '_final'
      const finalSetting = api(finalSettingID)
      if (!_.isUndefined(finalSetting)) {
        if (!_.isUndefined(parentSettingData.connected_fields) && !_.isEmpty(parentSettingData.connected_fields)) {
          finalSetting.set(finalValue)
        } else {
          finalSetting.set('')
        }
      }
    }
  }

  const bindConnectedFields = function () {
    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each(settingIDs, function (parentSettingID) {
      if (typeof apiSettings[parentSettingID] !== 'undefined') {
        const parentSettingData = apiSettings[parentSettingID]
        const parentSetting = api(parentSettingID)

        if (!_.isUndefined(parentSettingData.connected_fields)) {
          customify.colorPalettes.connectedFieldsCallbacks[parentSettingID] = getMasterFieldCallback(parentSettingData, parentSettingID)
          parentSetting.bind(customify.colorPalettes.connectedFieldsCallbacks[parentSettingID])
          _.each(parentSettingData.connected_fields, function (connectedFieldData) {
            const connectedSettingID = connectedFieldData.setting_id
            const connectedSetting = api(connectedSettingID)

            if (typeof connectedSetting !== 'undefined') {
              customify.colorPalettes.connectedFieldsCallbacks[connectedSettingID] = toggleAlteredClassOnMasterControls
              connectedSetting.bind(customify.colorPalettes.connectedFieldsCallbacks[connectedSettingID])
            }
          })
        }
      }
    })
  }

  const unbindConnectedFields = function () {
    _.each(customify.colorPalettes.connectedFieldsCallbacks, function (callback, settingID) {
      const setting = api(settingID)
      setting.unbind(callback)
    })
    customify.colorPalettes.connectedFieldsCallbacks = {}
  }

  // alter connected fields of the master colors controls depending on the selected palette variation
  const getCurrentVariation = () => {
    const setting = api('sm_color_palette_variation')

    if (_.isUndefined(setting)) {
      return defaultVariation
    }

    const variation = setting()

    if (!customify.colorPalettes.variations.hasOwnProperty(variation)) {
      return defaultVariation
    }

    return variation
  }

  const getSwapMap = (variation) => {
    if (!customify.colorPalettes.variations.hasOwnProperty(variation)) {
      return defaultVariation
    }

    return customify.colorPalettes.variations[variation]
  }





  const createCurrentPaletteControls = () => {
    const $palette = $('.c-color-palette')
    const $fields = $palette.find('.c-color-palette__fields').find('input')

    if (!$palette.length) {
      return
    }

    const $colors = $palette.find('.sm-color-palette__color')

    $colors.each((i, obj) => {
      const $obj = $(obj)
      const settingID = $obj.data('setting')
      const $input = $fields.filter('.' + settingID)
      const setting = api(settingID)

      $obj.data('target', $input)

      if ($obj.hasClass('js-no-picker')) { return }

      $input.iris({
        change: (event, ui) => {
          const currentColor = ui.color.toString()

          $obj.css('color', filterColor(currentColor))

          filteredColors[settingID] = filterColor(currentColor)
          setting.set(currentColor)

          if (event.originalEvent.type !== 'external') {
            $palette.find('.sm-color-palette__color.' + settingID).removeClass('altered')
          }

          setPalettesOnConnectedFields()
        },
      })

      $obj.find('.iris-picker').on('click', function (e) {
        e.stopPropagation()
        e.preventDefault()
      })

      const showColorPicker = () => {
        $colors.not($obj).each(function (i, obj) {
          $(obj).data('target').not($input).hide()
        })
        $input.show().focus()
      }

      $obj.on('click', (e) => {
        e.stopPropagation()
        e.preventDefault()

        if ($input.is(':visible')) {
          $input.iris('hide')
          $input.hide()
          $colors.removeClass('active inactive')
        } else {
          if ($obj.is('.altered')) {
            confirmChanges(showColorPicker)
          } else {
            showColorPicker()
          }
        }
      })

      $input.on('click', (e) => {
        e.stopPropagation()
        e.preventDefault()
      })

      $input.on('focus', (e) => {
        $colors.not($obj).addClass('inactive').removeClass('active')
        $obj.addClass('active').removeClass('inactive')

        $colors.not($obj).each(function (i, other) {
          $(other).data('target').iris('hide')
        })

        const $iris = $input.next('.iris-picker')
        const paletteWidth = $palette.find('.c-color-palette__colors').outerWidth()
        const $visibleColors = $colors.filter(':visible')
        const index = $visibleColors.index($obj)

        $iris.css('left', (paletteWidth - 200) * index / ($visibleColors.length - 1))

        showOldColors()

        $input.iris('show')
      })

      $input.on('focusout', (e) => {
        showNewColors()
      })
    })

    $('body').on('click', function () {
      $colors.removeClass('active inactive')
      $colors.each(function (i, obj) {
        const $input = $(obj).data('target')

        if (!$(obj).hasClass('js-no-picker')) {
          $input.iris('hide')
        }
        $input.hide()
      })
    })
  }

  const showNewColors = function () {
    _.each(customify.colorPalettes.masterSettingIds, function (id) {
      $('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', getFilteredColor(id))
    })
  }

  const showOldColors = function () {
    _.each(customify.colorPalettes.masterSettingIds, function (id) {
      const setting = api(id)
      const initialColor = setting()
      $('.c-color-palette').find('.sm-color-palette__color.' + id).css('color', initialColor)
    })
  }

  const onPaletteChange = function () {
    $(this).trigger('customify:preset-change')
    updateFilteredColors()
    reinitializeConnectedFields()
  }

  // this function goes through all the connected fields and adds swatches to the default color picker for all the colors in the current color palette
  const setPalettesOnConnectedFields = _.debounce(() => {
    let $targets = $()
    // loop through the master settings
    _.each(customify.colorPalettes.masterSettingIds, function (parentSettingID) {
      if (typeof apiSettings[parentSettingID] !== 'undefined') {
        const parentSettingData = apiSettings[parentSettingID]

        if (!_.isUndefined(parentSettingData.connected_fields)) {
          // loop through all the connected fields and search the element on which the iris plugin has been initialized
          _.each(parentSettingData.connected_fields, function (connectedFieldData) {
            // the connected_setting_id is different than the actual id attribute of the element we're searching for
            // so we have to do some regular expressions
            const connectedSettingID = connectedFieldData.setting_id
            const matches = connectedSettingID.match(/\[(.*?)\]/)

            if (matches) {
              const targetID = matches[1]
              const $target = $('.customize-control-color').filter('[id*="' + targetID + '"]').find('.wp-color-picker')
              $targets = $targets.add($target)
            }
          })
        }
      }
    })
    // apply the current color palettes to all the elements found
    $targets.iris({palettes: getCurrentPaletteColors()})
  }, 30)

  const toggleAlteredClassOnMasterControls = _.debounce(() => {
    const alteredSettings = []
    let alteredSettingsSelector

    _.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
      let connectedFields = apiSettings[masterSettingId]['connected_fields']
      const masterSettingValue = api(masterSettingId)()
      let connectedFieldsWereAltered = false

      if (!_.isUndefined(connectedFields) && !Array.isArray(connectedFields)) {
        connectedFields = Object.keys(connectedFields).map(function (key) {
          return connectedFields[key]
        })
      }

      if (!_.isUndefined(connectedFields) && connectedFields.length) {
        _.each(connectedFields, function (connectedField) {
          const connectedSettingId = connectedField.setting_id
          const connectedSetting = api(connectedSettingId)

          if (typeof connectedSetting !== 'undefined') {
            const connectedFieldValue = connectedSetting()

            if (typeof connectedFieldValue === 'string' && connectedFieldValue.toLowerCase() !== filterColor(masterSettingValue).toLowerCase()) {
              connectedFieldsWereAltered = true
            }
          }
        })

        if (connectedFieldsWereAltered) {
          alteredSettings.push(masterSettingId)
        }
      }
    })

    alteredSettingsSelector = '.' + alteredSettings.join(', .')

    $('.c-color-palette .color').removeClass('altered')

    if (alteredSettings.length) {
      $('.c-color-palette .color').filter(alteredSettingsSelector).addClass('altered')
    }

  }, 30)

  const toggleHiddenClassOnMasterControls = _.debounce(() => {
    const optionsToShow = []
    let optionsSelector

    _.each(customify.colorPalettes.masterSettingIds, function (masterSettingId) {
      const connectedFields = apiSettings[masterSettingId]['connected_fields']

      if (!_.isUndefined(connectedFields) && !_.isEmpty(connectedFields)) {
        optionsToShow.push(masterSettingId)
      }
    })

    if (!_.isEmpty(optionsToShow)) {
      optionsSelector = '.' + optionsToShow.join(', .')
    } else {
      optionsSelector = '*'
    }

    $('.sm-palette-filter .color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
    $('.sm-color-palette__color').addClass('hidden').filter(optionsSelector).removeClass('hidden')
    $('.js-color-palette .palette__item').addClass('hidden').filter(optionsSelector).removeClass('hidden')
  }, 30)

  const refreshCurrentPaletteControl = () => {
    toggleAlteredClassOnMasterControls()
    toggleHiddenClassOnMasterControls()
    setPalettesOnConnectedFields()
    showNewColors()
  }

  const swapConnectedFields = (settings, swapMap) => {
    // @todo This is weird. We should be able to have the settings in the proper form.
    const newSettings = JSON.parse(JSON.stringify(settings))
    const oldSettings = JSON.parse(JSON.stringify(settings))

    _.each(swapMap, function (fromArray, to) {
      if (typeof newSettings[to] !== 'undefined') {
        let newConnectedFields = []
        if (fromArray instanceof Array) {
          _.each(fromArray, function (from) {
            let oldConnectedFields
            if (_.isUndefined(oldSettings[from]['connected_fields'])) {
              oldSettings[from]['connected_fields'] = []
            }
            oldConnectedFields = Object.values(oldSettings[from]['connected_fields'])
            newConnectedFields = newConnectedFields.concat(oldConnectedFields)
          })
        }
        newSettings[to]['connected_fields'] = Object.keys(newConnectedFields).map(function (key) {
          return newConnectedFields[key]
        })
      }
    })
    return _.clone(newSettings)
  }

  const moveConnectedFields = (oldSettings, from, to, ratio) => {

    const settings = _.clone(oldSettings)

    if (!_.isUndefined(settings[to]) && !_.isUndefined(settings[from])) {

      if (_.isUndefined(settings[from]['connected_fields'])) {
        settings[from]['connected_fields'] = []
      }

      if (_.isUndefined(settings[to]['connected_fields'])) {
        settings[to]['connected_fields'] = []
      }

      const oldFromConnectedFields = Object.values(settings[from]['connected_fields'])
      const oldToConnectedFields = Object.values(settings[to]['connected_fields'])
      const oldConnectedFields = oldToConnectedFields.concat(oldFromConnectedFields)
      const count = Math.round(ratio * oldConnectedFields.length)

      let newToConnectedFields = oldConnectedFields.slice(0, count)
      const newFromConnectedFields = oldConnectedFields.slice(count)

      newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
        return newToConnectedFields[key]
      })
      newToConnectedFields = Object.keys(newToConnectedFields).map(function (key) {
        return newToConnectedFields[key]
      })

      settings[to]['connected_fields'] = newToConnectedFields
      settings[from]['connected_fields'] = newFromConnectedFields
    }

    return settings
  }

  const reloadConnectedFields = () => {
    let tempSettings = JSON.parse(JSON.stringify(customify.settingsClone))
    const diversityOptions = $('[name*="sm_color_diversity"]')
    const colorationOptions = $('[name*="sm_coloration_level"]')

    const hasDiversityOption = !!diversityOptions.length
    const isDefaultDiversitySelected = typeof diversityOptions.filter(':checked').data('default') !== 'undefined'
    const isDefaultDiversity = hasDiversityOption ? isDefaultDiversitySelected : true

    const hasColorationOptions = !!colorationOptions.length
    const isDefaultColorationSelected = typeof $('[name*="sm_coloration_level"]:checked').data('default') !== 'undefined'
    const isDefaultColoration = hasColorationOptions ? isDefaultColorationSelected : true

    const selectedDiversity = hasDiversityOption ? $('[name*="sm_color_diversity"]:checked').val() : api('sm_color_diversity')()

    if (!isDefaultDiversity || !isDefaultColoration) {
      const switchRatio = $( switchColorSelector ).val() / 100;
      const selectRatio = $( selectColorSelector ).val() / 100;
      const primaryRatio = $( primaryColorSelector ).val() / 100;
      const secondaryRatio = $( secondaryColorSelector ).val() / 100;
      const tertiaryRatio = $( tertiaryColorSelector ).val() / 100;

      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio)

      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio)
      tempSettings = moveConnectedFields(tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio)

      const diversity_variation = getSwapMap('color_diversity_low')
      tempSettings = swapConnectedFields(tempSettings, diversity_variation)

      if (selectedDiversity === 'medium') {
        tempSettings = moveConnectedFields(tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.5)
      }

      if (selectedDiversity === 'high') {
        tempSettings = moveConnectedFields(tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.67)
        tempSettings = moveConnectedFields(tempSettings, 'sm_color_secondary', 'sm_color_tertiary', 0.50)
      }
    }

    const shuffle = $('[name*="sm_shuffle_colors"]:checked').val()
    if (shuffle !== 'default') {
      const shuffleVariation = getSwapMap('shuffle_' + shuffle)
      tempSettings = swapConnectedFields(tempSettings, shuffleVariation)
    }

    const dark_mode = $('[name*="sm_dark_mode_control"]:checked').val()
    if (dark_mode === 'on') {
      const dark_mmode_variation = getSwapMap('dark')
      tempSettings = swapConnectedFields(tempSettings, dark_mmode_variation)
    }

    var settingIDs = customify.colorPalettes.masterSettingIds.concat( newMasterIDs );

    _.each(settingIDs, function (masterSettingId) {
      apiSettings[masterSettingId] = tempSettings[masterSettingId]
    })
  }

  const applyColorationValueToFields = () => {
    const $selectedColoration = $( '[name*="sm_coloration_level"]:checked' );
    const coloration = $selectedColoration.val();

    if ( typeof $selectedColoration.data( 'default' ) !== 'undefined' ) {

      const sliders = [
        'sm_dark_color_primary_slider',
        'sm_dark_color_secondary_slider',
        'sm_dark_color_tertiary_slider',
      ];

      _.each( sliders, function( sliderID ) {
        const sliderSetting = customify.config.settings[sliderID];
        wp.customize( sliderID ).set( sliderSetting.default );
        $( `#_customize-input-${ sliderID }_control` ).val( sliderSetting.default );
      } )
    } else {
      $colorSliders.val( parseFloat( coloration ) )
    }
    reinitializeConnectedFields()
  }

  const reinitializeConnectedFields = () => {
    reloadConnectedFields()
    unbindConnectedFields()
    bindConnectedFields()
    refreshCurrentPaletteControl()
    resetSettings()
  }

  const confirmChanges = (callback) => {
    const altered = !!$('.c-color-palette .color.altered').length
    let confirmed = true

    if (altered) {
      confirmed = confirm('One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action.')
    }

    if (!altered || confirmed) {
      if (typeof callback === 'function') {
        callback()
      }
      return true
    }

    return false
  }

  const bindEvents = () => {
    const paletteControlSelector = '.c-color-palette__control'
    const $paletteControl = $(paletteControlSelector)
    const variation = getCurrentVariation()

    $paletteControl.removeClass('active')
    $paletteControl.filter('.variation-' + variation).addClass('active')

    $(document).on('click', '.js-color-palette input', function (e) {
      if (!confirmChanges(onPaletteChange.bind(this))) {
        e.preventDefault()
      }
    })

    $('[for*="sm_palette_filter"], [for*="sm_coloration_level"], [for*="sm_color_diversity"], [for*="sm_shuffle_colors"], [for*="sm_dark_mode"]').on('click', function (e) {
      if (!confirmChanges()) {
        e.preventDefault()
      }
    })

    $colorSliders.on( 'change', reinitializeConnectedFields );

    $('[name*="sm_coloration_level"]').on('change', applyColorationValueToFields)
    $('[name*="sm_color_diversity"]').on('change', reinitializeConnectedFields)
    $('[name*="sm_shuffle_colors"]').on('change', reinitializeConnectedFields)
    $('[name*="sm_dark_mode"]').on('change', reinitializeConnectedFields)
    $('[name*="sm_palette_filter"]').on('change', () => {
      updateFilteredColors()
      reinitializeConnectedFields()
    })
  }

  const updateFilterPreviews = _.debounce(() => {
    $('.sm-palette-filter').each(function () {
      const $filters = $(this).find('input')

      $filters.each(function (i, obj) {
        const $input = $(obj)
        const $label = $input.next('label')
        const label = $input.val()
        const $colors = $label.find('.sm-color-palette__color')

        $colors.each(function (j, color) {
          const $color = $(color)
          const settingID = $color.data('setting')
          const setting = api(settingID)
          const originalColor = setting()

          $color.css('color', filterColor(originalColor, label))
        })
      })
    })
  }, 30)

  api.bind('ready', function () {
    // We need to do this here to be sure the data is available.
    apiSettings = api.settings.settings

    setupGlobals()

    createCurrentPaletteControls()

    reloadConnectedFields()
    updateFilteredColors()
    unbindConnectedFields()
    bindConnectedFields()
    refreshCurrentPaletteControl()

    updateFilterPreviews()

    bindEvents()
  })

  return {}
}() )
