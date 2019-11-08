(
	function ($, exports, wp) {
		var api = wp.customize
		var $window = $(window)

		// when the customizer is ready prepare our fields events
		wp.customize.bind('ready', function () {
			var timeout = null

			// Create a stack of callbacks bound to parent settings to be able to unbind them
			// when altering the connected_fields attribute.
			if (typeof window.connectedFieldsCallbacks === 'undefined') {
				window.connectedFieldsCallbacks = {}
			}

			// add ace editors
			$('.customify_ace_editor').each(function (key, el) {
				const id = $(this).attr('id'),
					css_editor = ace.edit(id)

				const editor_type = $(this).data('editor_type')
				// init the ace editor
				css_editor.setTheme('ace/theme/github')
				css_editor.getSession().setMode('ace/mode/' + editor_type)

				// hide the textarea and enable the ace editor
				const textarea = $('#' + id + '_textarea').hide()
				css_editor.getSession().setValue(textarea.val())

				// each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
				// if the user types faster than this delay then reset it
				css_editor.getSession().on('change', function (e) {
					if (timeout !== null) {
						clearTimeout(timeout)
						timeout = null
					} else {
						timeout = setTimeout(function () {
							//var state = css_editor.session.getState();
							textarea.val(css_editor.getSession().getValue())
							textarea.trigger('change')
						}, 1500)
					}
				})
			})

			// simple select2 field
			$('.customify_select2').select2()

			setTimeout(function () {
				CustomifyFontSelectFields.init()
			}, 333)

			prepare_typography_field()

			/**
			 * Make the customizer save on CMD/CTRL+S action
			 * This is awesome!!!
			 */
			$(window).bind('keydown', function (event) {
				if (event.ctrlKey || event.metaKey) {
					switch (String.fromCharCode(event.which).toLowerCase()) {
						case 's':
							event.preventDefault()
							api.previewer.save()
							break
					}
				}
			})

			// for each range input add a value preview output
			$('.accordion-section-content[id*="' + customify_settings.options_name + '"], #sub-accordion-section-style_manager_section').each(function () {

				// Initialize range fields logic
				customifyHandleRangeFields(this)
			})

			if ($('button[data-action="reset_customify"]').length > 0) {
				// reset_button
				$(document).on('click', '#customize-control-reset_customify button', function (ev) {
					ev.preventDefault()

					var iAgree = confirm('Do you really want to reset to defaults all the fields? Watch out, this will reset all your Customify options and will save them!')

					if (!iAgree) {
						return
					}

					$.each(api.settings.controls, function (key, ctrl) {
						const setting_id = key.replace('_control', '')
						const setting = customify_settings.settings[setting_id]

						if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
							api_set_setting_value(setting_id, setting.default)
						}
					})

					api.previewer.save()
				})

				// add a reset button for each panel
				$('.panel-meta').each(function (el, key) {
					const container = $(this).parents('.control-panel'),
						id = container.attr('id')

					if (typeof id !== 'undefined') {
						const panel_id = id.replace('accordion-panel-', '')
						$(this).parent().append('<button class="reset_panel button" data-panel="' + panel_id + '">Panel\'s defaults</button>')
					}
				})

				// reset panel
				$(document).on('click', '.reset_panel', function (e) {
					e.preventDefault()

					const panel_id = $(this).data('panel'),
						panel = api.panel(panel_id),
						sections = panel.sections(),
						iAgree = confirm('Do you really want to reset ' + panel.params.title + '?')

					if (!iAgree) {
						return
					}
					if (sections.length > 0) {
						$.each(sections, function () {
							//var settings = this.settings();
							const controls = this.controls()

							if (controls.length > 0) {
								$.each(controls, function (key, ctrl) {
									const setting_id = ctrl.id.replace('_control', ''),
										setting = customify_settings.settings[setting_id]

									if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
										api_set_setting_value(setting_id, setting.default)
									}
								})
							}
						})
					}
				})

				//add reset section
				$('.accordion-section-content').each(function (el, key) {
					const section_id = $(this).attr('id')

					if ((
						(
							!_.isUndefined(section_id)
						) ? section_id.indexOf(customify_settings.options_name) : -1
					) === -1) {
						return
					}

					if (!_.isUndefined(section_id) && section_id.indexOf('sub-accordion-section-') > -1) {
						const id = section_id.replace('sub-accordion-section-', '')
						$(this).append('<button class="reset_section button" data-section="' + id + '">Reset All Options for This Section</button>')
					}
				})

				// reset section event
				$(document).on('click', '.reset_section', function (e) {
					e.preventDefault()

					const section_id = $(this).data('section'),
						section = api.section(section_id),
						controls = section.controls()

					const iAgree = confirm('Do you really want to reset ' + section.params.title + '?')

					if (!iAgree) {
						return
					}

					if (controls.length > 0) {
						$.each(controls, function (key, ctrl) {
							const setting_id = ctrl.id.replace('_control', ''),
								setting = customify_settings.settings[setting_id]

							if (!_.isUndefined(setting) && !_.isUndefined(setting.default)) {
								api_set_setting_value(setting_id, setting.default)
							}
						})
					}
				})
			}

			$(document).on('change', '.customify_typography_font_subsets', function (ev) {

				const $input = $(this).parents('.options').siblings('.customify_typography').children('.customify_typography_values');
				let current_val = $input.val()

				current_val = JSON.parse(decodeURIComponent(current_val))

				//maybe the selected option holds a JSON in its value
				current_val.selected_subsets = maybeJsonParse($(this).val())

				$input.val(encodeURIComponent(JSON.stringify(current_val)))

				$input.trigger('change')
			})

			$(document).on('change', '.customify_typography_font_weight', function (ev) {

				 const $input = $(this).parents('.options').siblings('.customify_typography').children('.customify_typography_values');
				 let current_val = $input.val();

				current_val = maybeJsonParse(current_val)
				// @todo currently the font weight selector works for one value only
				// maybe make this a multiselect

				//maybe the selected option holds a JSON in its value
				current_val.selected_variants = {0: maybeJsonParse($(this).val())}

				$input.val(encodeURIComponent(JSON.stringify(current_val)))
				$input.trigger('change')
			})

			$('body').on('customify:preset-change', function (e) {
				const data = $(e.target).data('options')

				if (!_.isUndefined(data)) {
					$.each(data, function (setting_id, value) {
						api_set_setting_value(setting_id, value)
					})
				}
			})

			$(document).on('change', 'select.js-customify-preset', function () {
				const $source = $(this)
				const $target = $source.children('[value="' + $source.val() + '"]')
				$target.trigger('customify:preset-change')
			})

			$(document).on('click', '.js-customify-preset input', function () {
				$(this).trigger('customify:preset-change')
			})

			customifyBackgroundJsControl.init()

			// sometimes a php save may be needed
			if (getUrlVars('save_customizer_once')) {
				api.previewer.save()
			}

			setTimeout(function () {
				customifyFoldingFields()
			}, 1000);

			// Handle the section tabs (ex: Layout | Fonts | Colors)
			(
				function () {
					const $navs = $('.js-section-navigation')

					$navs.each(function () {
						const $nav = $(this)
						const $title = $nav.parents('.accordion-section-content').find('.customize-section-title')

						$nav.closest('.customize-control').addClass('screen-reader-text')
						$title.append($nav).parent().addClass('has-nav')
					})

					$('.js-section-navigation a').on('click', function (e) {
						e.preventDefault()

						const $sidebar = $(this).parents('.customize-pane-child'),
              $parent = $(this).parents('.accordion-section-content'),
              href = $.attr(this, 'href')

						if (href != '#') {
							$sidebar.animate({
								scrollTop: $($.attr(this, 'href')).position().top - $parent.find('.customize-section-title').outerHeight()
							}, 500)
						}
					})
				}
			)();

      (function() {
        var $allCheckboxes = $( '.js-font-option-toggle' );
        // Close a font field when clicking on another field
        $allCheckboxes.on( 'click', function() {
          var $checkbox = $( this );
          if ( $checkbox.prop( 'checked' ) === true ) {
            $allCheckboxes.not( $checkbox ).prop( 'checked', false );
          }
        } )
      })();

			// Bind any connected fields, except those in the Style Manager.
			// Those are handled by the appropriate Style Manager component (Color Palettes, Font Palettes, etc ).
			bindConnectedFields()

		})

		const getConnectedFieldsCallback = function (parent_setting_data, parent_setting_id) {
			return function (new_value, old_value) {
				_.each(parent_setting_data.connected_fields, function (connected_field_data) {
					if (_.isUndefined(connected_field_data) || _.isUndefined(connected_field_data.setting_id) || !_.isString(connected_field_data.setting_id)) {
						return
					}
					const setting = wp.customize(connected_field_data.setting_id)
					if (_.isUndefined(setting)) {
						return
					}
					setting.set(new_value)
				})
			}
		}

		const bindConnectedFields = function () {
			_.each(wp.customize.settings.settings, function (parent_setting_data, parent_setting_id) {
				// We don't want to handle the binding of the Style Manager settings
				if (typeof ColorPalettes !== 'undefined'
					&& typeof ColorPalettes.masterSettingIds !== 'undefined'
					&& _.contains(ColorPalettes.masterSettingIds, parent_setting_id)) {
					return
				}
				if (typeof FontPalettes !== 'undefined'
					&& typeof FontPalettes.masterSettingIds !== 'undefined'
					&& _.contains(FontPalettes.masterSettingIds, parent_setting_id)) {
					return
				}

				let parent_setting = wp.customize(parent_setting_id)
				if (typeof parent_setting_data.connected_fields !== 'undefined') {
					connectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback(parent_setting_data, parent_setting_id)
					parent_setting.bind(connectedFieldsCallbacks[parent_setting_id])
				}
			})
		}

		const unbindConnectedFields = function () {
			_.each(wp.customize.settings.settings, function (parent_setting_data, parent_setting_id) {
				// We don't want to handle the binding of the Style Manager settings
				if (typeof ColorPalettes !== 'undefined'
					&& typeof ColorPalettes.masterSettingIds !== 'undefined'
					&& _.contains(ColorPalettes.masterSettingIds, parent_setting_id)) {
					return
				}
				if (typeof FontPalettes !== 'undefined'
					&& typeof FontPalettes.masterSettingIds !== 'undefined'
					&& _.contains(FontPalettes.masterSettingIds, parent_setting_id)) {
					return
				}

				let parent_setting = wp.customize(parent_setting_id)
				if (typeof parent_setting_data.connected_fields !== 'undefined' && typeof connectedFieldsCallbacks[parent_setting_id] !== 'undefined') {
					parent_setting.unbind(connectedFieldsCallbacks[parent_setting_id])
				}
				delete connectedFieldsCallbacks[parent_setting_id]
			})
		}

		const customifyHandleRangeFields = function (el) {

			// For each range input add a number field (for preview mainly - but it can also be used for input)
      $( el ).find( 'input[type="range"]' ).each( function() {
        var $range = $( this ),
            $number = $range.siblings( '.range-value' );

        if ( ! $number.length ) {
					$number = $range.clone();

          $number
            .attr( 'type', 'number' )
            .attr( 'class', 'range-value' )
            .removeAttr( 'data-field' )
            .insertAfter( $range );
				}

        function hasValidValue() {
          var min = $number.attr( 'min' );
          var max = $number.attr( 'max' );
          var value = $number.val();

          if ( min !== undefined && parseFloat( min ) > parseFloat( value ) ) {
            return false;
          }

          if ( max !== undefined && parseFloat( max ) < parseFloat( value ) ) {
            return false;
          }

          return true;
        }

        const debouncedRangeUpdate = _.debounce( function() {
          if ( ! hasValidValue() ) {
            $number.addClass( 'animated error-shake' );
            setTimeout( function() {
              $number.removeClass( 'animated error-shake' );
            }, 300 ) // The animation has a 1 second duration.
          }
        }, 300 );

        // Update the range field when changing the number
        $number.on( 'change keyup', debouncedRangeUpdate );

        // Update the number field when changing the range
        $range.on( 'input', function() {
          $number.val( $range.val() ).change();
        } );
			})
		}

		/**
		 * This function will search for all the interdependend fields and make a bound between them.
		 * So whenever a target is changed, it will take actions to the dependent fields.
		 * @TODO  this is still written in a barbaric way, refactor when needed
		 */
		const customifyFoldingFields = function () {

			if (_.isUndefined(customify_settings) || _.isUndefined(customify_settings.settings)) {
				return // bail
			}

			/**
			 * Let's iterate through all the customify settings and gather all the fields that have a "show_if"
			 * property set.
			 *
			 * At the end `targets` will hold a list of [ target : [field, field,...], ... ]
			 * so when a target is changed we will change all the fields.
			 */
			var targets = {}

			$.fn.reactor.defaults.compliant = function () {
				$(this).slideDown()
				// $(this).animate({opacity: 1});
				$(this).find(':disabled').attr({disabled: false})
			}

			$.fn.reactor.defaults.uncompliant = function () {
				$(this).slideUp()
				// $(this).animate({opacity: 0.25});
				$(this).find(':enabled').attr({disabled: true})
			}

			let IS = $.extend({}, $.fn.reactor.helpers)

			let bind_folding_events = function (parent_id, field, relation) {

				let key = null

				if (_.isString(field)) {
					key = field
				} else if (!_.isUndefined(field.id)) {
					key = field.id
				} else if (isString(field[0])) {
					key = field[0]
				} else {
					return // no key, no fun
				}

				let value = 1, // by default we use 1 the most used value for checkboxes or inputs
					compare = '==', // ... ye
					action = 'show',
					between = [0, 1] // can only be `show` or `hide`

				const target_key = customify_settings.options_name + '[' + key + ']'
				const target_type = customify_settings.settings[target_key].type

				// we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
				// but we also support a non-associative array like `array( $id, $value, $compare )`
				if (!_.isUndefined(field.value)) {
					value = field.value
				} else if (!_.isUndefined(field[1]) && !_.isString(field[1])) {
					value = field[1]
				}

				if (!_.isUndefined(field.compare)) {
					compare = field.compare
				} else if (!_.isUndefined(field[2])) {
					compare = field[2]
				}

				if (!_.isUndefined(field.action)) {
					action = field.action
				} else if (!_.isUndefined(field[3])) {
					action = field[3]
				}

				// a field can also overwrite the parent relation
				if (!_.isUndefined(field.relation)) {
					action = field.relation
				} else if (!_.isUndefined(field[4])) {
					action = field[4]
				}

				if (!_.isUndefined(field.between)) {
					between = field.between
				}

				/**
				 * Now for each target we have, we will bind a change event to hide or show the dependent fields
				 */
				const target_selector = '[data-customize-setting-link="' + customify_settings.options_name + '[' + key + ']"]'

				switch (target_type) {
					case 'checkbox':
						$(parent_id).reactIf(target_selector, function () {
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
							$(parent_id).reactIf(target_selector, function () {
								return (
									value.indexOf($(target_selector + ':checked').val()) !== -1
								)
							})
						} else { // in any other case we use a simple == comparison
							$(parent_id).reactIf(target_selector, function () {
								return $(target_selector + ':checked').val() == value
							})
						}
						break

					case 'range':
						const x = IS.Between(between[0], between[1])

						$(parent_id).reactIf(target_selector, x)
						break

					default:
						// in case of an array of values we use the ( val in array) condition
						if (_.isObject(value)) {
							$(parent_id).reactIf(target_selector, function () {
								return (
									value.indexOf($(target_selector).val()) !== -1
								)
							})
						} else { // in any other case we use a simple == comparison
							$(parent_id).reactIf(target_selector, function () {
								return $(target_selector).val() == value
							})
						}
						break
				}

				$(target_selector).trigger('change')
				$('.reactor').trigger('change.reactor') // triggers all events on load
			}

			$.each(customify_settings.settings, function (id, field) {
				/**
				 * Here we have the id of the fields. but we know for sure that we just need his parent selector
				 * So we just create it
				 */
				let parent_id = id.replace('[', '-')
				parent_id = parent_id.replace(']', '')
				parent_id = '#customize-control-' + parent_id + '_control'

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
						bind_folding_events(parent_id, field.show_if, relation)
					} else if (_.isObject(field.show_if)) {
						$.each(field.show_if, function (i, j) {
							bind_folding_events(parent_id, j, relation)
						})
					}
				}
			})
		}

		const get_typography_font_family = function ($el) {

			let font_family_value = $el.val()
			// first time this will not be a json so catch that error
			try {
				font_family_value = JSON.parse(font_family_value)
			} catch (e) {
				return {font_family: font_family_value}
			}

			if (!_.isUndefined(font_family_value.font_family)) {
				return font_family_value.font_family
			}

			return false
		}

		// get each typography field and bind events
		// @todo Are we still using the typography field since we have the font field?
    // Yes we do, in older themes.
		const prepare_typography_field = function () {

			const $typos = $('.customify_typography_font_family')

			$typos.each(function () {
				var font_family_select = this,
					$input = $(font_family_select).siblings('.customify_typography_values')
				// on change
				$(font_family_select).on('change', function () {
					update_siblings_selects(font_family_select)
					$input.trigger('change')
				})
				update_siblings_selects(font_family_select)
			})
		}

		const api_set_setting_value = function (setting_id, value) {
			let setting = api(setting_id),
				field = $('[data-customize-setting-link="' + setting_id + '"]'),
				field_class = $(field).parent().attr('class')

			// Legacy field type
			if (!_.isUndefined(field_class) && field_class === 'customify_typography') {

				let family_select = field.siblings('select')

				if (_.isString(value)) {
					let this_option = family_select.find('option[value="' + value + '"]')
					$(this_option[0]).attr('selected', 'selected')
					update_siblings_selects(family_select)
				} else if (_.isObject(value)) {
					let this_family_option = family_select.find('option[value="' + value['font_family'] + '"]')

					$(this_family_option[0]).attr('selected', 'selected')

					update_siblings_selects(this_family_option)

					setTimeout(function () {
						let weight_select = field.parent().siblings('.options').find('.customify_typography_font_weight'),
							this_weight_option = weight_select.find('option[value="' + value['selected_variants'] + '"]')

						$(this_weight_option[0]).attr('selected', 'selected')

						update_siblings_selects(this_family_option)

						weight_select.trigger('change')
					}, 300)
				}

				family_select.trigger('change')

			} else if (!_.isUndefined(field_class) && field_class === 'font-options__wrapper') {

				// if the value is a simple string it must be the font family
				if (_.isString(value)) {
					let option = field.parent().find('option[value="' + value + '"]')

					option.attr('selected', 'selected')
					// option.parents('select').trigger('change');
				} else if (_.isObject(value)) {
					// Find the options list wrapper
					let optionsList = field.parent().children('.font-options__options-list')

					if (optionsList.length) {
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
									mappedKey = 'selected_variants'
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
							let subField = optionsList.find('[data-field="' + mappedKey + '"]')
							if (subField.length) {
								subField.val(val)
								subField.trigger('change')
							}
						})
					}
				}

			} else {
				setting.set(value)
			}
		}

		const update_siblings_selects = function (font_select) {
			let selected_font = $(font_select).val(),
				$input = $(font_select).siblings('.customify_typography_values'),
				current_val = $input.attr('value')

			if (current_val === '[object Object]') {
				current_val = $input.data('default')
			} else if (_.isString(current_val) && !isJsonString(current_val)) {
				// a rare case when the value isn't a json but is a representative string like [family,weight]
				current_val = current_val.split(',')
				let new_current_value = {}
				if (!_.isUndefined(current_val[0])) {
					new_current_value['font_family'] = current_val[0]
				}

				if (!_.isUndefined(current_val[1])) {
					new_current_value['selected_variants'] = current_val[1]
				}

				current_val = JSON.stringify(new_current_value)
			}

			let $font_weight = $(font_select).parent().siblings('ul.options').find('.customify_typography_font_weight'),
				$font_subsets = $(font_select).parent().siblings('ul.options').find('.customify_typography_font_subsets')

			try {
				current_val = JSON.parse(decodeURIComponent(current_val))
			} catch (e) {

				// in case of an error, force the rebuild of the json
				if (_.isUndefined($(font_select).data('bound_once'))) {

					$(font_select).data('bound_once', true)

					$(font_select).change()
					$font_weight.change()
					$font_subsets.change()
				}
			}

			// first try to get the font from sure sources, not from the recommended list.
			let option_data = $(font_select).find(':not(optgroup[label=Recommended]) option[value="' + selected_font + '"]')
			// however, if there isn't an option found, get what you can
			if (option_data.length < 1) {
				option_data = $(font_select).find('option[value="' + selected_font + '"]')
			}

			if (option_data.length > 0) {

				let font_type = option_data.data('type'),
					value_to_add = {'type': font_type, 'font_family': selected_font},
					variants = null,
					subsets = null

				if (font_type == 'std') {
					variants = {
						0: '100',
						1: '200',
						3: '300',
						4: '400',
						5: '500',
						6: '600',
						7: '700',
						8: '800',
						9: '900'
					}
					if (!_.isUndefined($(option_data[0]).data('variants'))) {
						//maybe the variants are a JSON
						variants = maybeJsonParse($(option_data[0]).data('variants'))
					}
				} else {
					//maybe the variants are a JSON
					variants = maybeJsonParse($(option_data[0]).data('variants'))

					//maybe the subsets are a JSON
					subsets = maybeJsonParse($(option_data[0]).data('subsets'))
				}

				// make the variants selector
				if (!_.isUndefined(variants) && !_.isNull(variants) && !_.isEmpty(variants)) {

					value_to_add['variants'] = variants
					// when a font is selected force the first weight to load
					value_to_add['selected_variants'] = {0: variants[0]}

					let variants_options = '',
						count_weights = 0

					if (_.isArray(variants) || _.isObject(variants)) {
						// Take each variant and produce the option markup
						$.each(variants, function (key, el) {
							let is_selected = ''
							if (_.isObject(current_val.selected_variants) && inObject(el, current_val.selected_variants)) {
								is_selected = ' selected="selected"'
							} else if (_.isString(current_val.selected_variants) && el === current_val.selected_variants) {
								is_selected = ' selected="selected"'
							}

							// initialize
							let variant_option_value = el,
								variant_option_display = el

							// If we are dealing with a object variant then it means things get tricky (probably it's our fault but bear with us)
							// This probably comes from our Fonto plugin - a font with individually named variants - hence each has its own font-family
							if (_.isObject(el)) {
								//put the entire object in the variation value - we will need it when outputting the custom CSS
								variant_option_value = encodeURIComponent(JSON.stringify(el))
								variant_option_display = ''

								//if we have weight and style then "compose" them into something standard
								if (!_.isUndefined(el['font-weight'])) {
									variant_option_display += el['font-weight']
								}

								if (_.isString(el['font-style']) && $.inArray(el['font-style'].toLowerCase(), [
									'normal',
									'regular'
								]) < 0) { //this comparison means it hasn't been found
									variant_option_display += el['font-style']
								}
							}

							variants_options += '<option value="' + variant_option_value + '"' + is_selected + '>' + variant_option_display + '</option>'
							count_weights++
						})
					}

					if (!_.isUndefined($font_weight)) {
						$font_weight.html(variants_options)
						// if there is no weight or just 1 we hide the weight select ... cuz is useless
						if ($(font_select).data('load_all_weights') === true || count_weights <= 1) {
							$font_weight.parent().css('display', 'none')
						} else {
							$font_weight.parent().css('display', 'inline-block')
						}
					}
				} else if (!_.isUndefined($font_weight)) {
					$font_weight.parent().css('display', 'none')
				}

				// make the subsets selector
				if (!_.isUndefined(subsets) && !_.isNull(subsets) && !_.isEmpty(subsets)) {

					value_to_add['subsets'] = subsets
					// when a font is selected force the first subset to load
					value_to_add['selected_subsets'] = {0: subsets[0]}
					let subsets_options = '',
						count_subsets = 0
					$.each(subsets, function (key, el) {
						let is_selected = ''
						if (_.isObject(current_val.selected_subsets) && inObject(el, current_val.selected_subsets)) {
							is_selected = ' selected="selected"'
						}

						subsets_options += '<option value="' + el + '"' + is_selected + '>' + el + '</option>'
						count_subsets++
					})

					if (!_.isUndefined($font_subsets)) {
						$font_subsets.html(subsets_options)

						// if there is no subset or just 1 we hide the subsets select ... cuz is useless
						if (count_subsets <= 1) {
							$font_subsets.parent().css('display', 'none')
						} else {
							$font_subsets.parent().css('display', 'inline-block')
						}
					}
				} else if (!_.isUndefined($font_subsets)) {
					$font_subsets.parent().css('display', 'none')
				}

				$input.val(encodeURIComponent(JSON.stringify(value_to_add)))
			}
		}

		/** Modules **/

		const customifyBackgroundJsControl = (
			function () {
				'use strict'

				function init () {
					// Remove the image button
					$('.customize-control-custom_background .remove-image, .customize-control-custom_background .remove-file').unbind('click').on('click', function (e) {
						removeImage($(this).parents('.customize-control-custom_background:first'))
						preview($(this))
						return false
					})

					// Upload media button
					$('.customize-control-custom_background .background_upload_button').unbind().on('click', function (event) {
						addImage(event, $(this).parents('.customize-control-custom_background:first'))
					})

					$('.customify_background_select').on('change', function () {
						preview($(this))
					})
				}

				// Add a file via the wp.media function
				function addImage (event, selector) {

					event.preventDefault()

					var frame
					var jQueryel = jQuery(this)

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
						title: jQueryel.data('choose'),

						// Customize the submit button.
						button: {
							// Set the text of the button.
							text: jQueryel.data('update')
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

						selector.find('.customify_background_input.background-image').val(attachment.attributes.url)

						if (!selector.find('.upload').hasClass('noPreview')) {
							selector.find('.preview_screenshot').empty().hide().append('<img class="preview_image" src="' + thumbSrc + '">').slideDown('fast')
						}
						//selector.find('.media_upload_button').unbind();
						selector.find('.remove-image').removeClass('hide')//show "Remove" button
						selector.find('.customify_background_select').removeClass('hide')//show "Remove" button

						preview(selector)
					})

					// Finally, open the modal.
					frame.open()
				}

				// Update the background preview
				function preview (selector) {

					let $parent = selector.parents('.customize-control-custom_background:first')

					if (selector.hasClass('customize-control-custom_background')) {
						$parent = selector
					}

					if ($parent.length > 0) {
						$parent = $($parent[0])
					} else {
						return
					}

					const image_holder = $parent.find('.background-preview')

					if (!image_holder) { // No preview present
						return
					}

					const the_id = $parent.find('.button.background_upload_button').data('setting_id'),
						this_setting = api.instance(the_id)

					let background_data = {}

					$parent.find('.customify_background_select, .customify_background_input').each(function () {
						var data = $(this).serializeArray()

						data = data[0]
						if (data && data.name.indexOf('[background-') != -1) {

							background_data[$(this).data('select_name')] = data.value

							//default_default[data.name] = data.value;
							//if (data.name == "background-image") {
							//	css += data.name + ':url("' + data.value + '");';
							//} else {
							//	css += data.name + ':' + data.value + ';';
							//}
						}
					})

					api.instance(the_id).set(background_data)
					//// Notify the customizer api about this change
					api.trigger('change')
					api.previewer.refresh()

					//image_holder.attr('style', css).fadeIn();
				}

				// Update the background preview
				function removeImage (parent) {
					const selector = parent.find('.upload_button_div')
					// This shouldn't have been run...
					if (!selector.find('.remove-image').addClass('hide')) {
						return
					}

					selector.find('.remove-image').addClass('hide')//hide "Remove" button
					parent.find('.customify_background_select').addClass('hide')

					selector.find('.upload').val('')
					selector.find('.upload-id').val('')
					selector.find('.upload-height').val('')
					selector.find('.upload-width').val('')
					parent.find('.customify_background_input.background-image').val('')

					let customizer_id = selector.find('.background_upload_button').data('setting_id'),
						this_setting = api.control(customizer_id + '_control'),
						current_vals = this_setting.setting(),
						screenshot = parent.find('.preview_screenshot'),
						to_array = $.map(current_vals, function (value, index) {
							return [value]
						})

					// Hide the screenshot
					screenshot.slideUp()
					selector.find('.remove-file').unbind()
					to_array['background-image'] = ''
					this_setting.setting(to_array)
				}

				return {
					init: init
				}
			}
		)(jQuery)

		/** HELPERS **/

		/**
		 * Function to check if a value exists in an object
		 * @param value
		 * @param obj
		 * @returns {boolean}
		 */
		const inObject = function (value, obj) {
			for (let k in obj) {
				if (!obj.hasOwnProperty(k)) {
					continue
				}
				if (_.isEqual(obj[k], value)) {
					return true
				}
			}
			return false
		}

		const maybeJsonParse = function (value) {
			let parsed

			//try and parse it, with decodeURIComponent
			try {
				parsed = JSON.parse(decodeURIComponent(value))
			} catch (e) {

				// in case of an error, treat is as a string
				parsed = value
			}

			return parsed
		}

		const getUrlVars = function (name) {
			let vars = [], hash
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

		const isJsonString = function (str) {
			try {
				JSON.parse(str)
			} catch (e) {
				return false
			}
			return true
		}
	}
)(jQuery, window, wp)
