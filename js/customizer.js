(function ( $, exports, wp ) {
	'use strict';

	var api = wp.customize;

	// when the customizer is ready prepare our fields events
	wp.customize.bind('ready', function () {
		var timeout = null;

		// add ace editors
		$('.customify_ace_editor').each(function ( key, el ) {
			var id = $(this).attr('id'),
				css_editor = ace.edit(id);

			var editor_type = $(this).data('editor_type');
			// init the ace editor
			css_editor.setTheme("ace/theme/github");
			css_editor.getSession().setMode("ace/mode/" + editor_type);

			// hide the textarea and enable the ace editor
			var textarea = $('#' + id + '_textarea').hide();
			css_editor.getSession().setValue(textarea.val());

			// each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
			// if the user types faster than this delay then reset it
			css_editor.getSession().on('change', function ( e ) {
				if ( timeout !== null ) {
					clearTimeout(timeout);
					timeout = null;
				} else {
					timeout = setTimeout(function () {
						//var state = css_editor.session.getState();
						textarea.val(css_editor.getSession().getValue());
						textarea.trigger('change');
					}, 1500);
				}
			});
		});

		// simple select2 field
		$('.customify_select2').select2();

		setTimeout(function () {
			customifyFontSelect.init(this);
		}, 333);

		prepare_typography_field();

		/**
		 * Make the customizer save on CMD/CTRL+S action
		 * This is awesome!!!
		 */
		$(window).bind('keydown', function ( event ) {
			if ( event.ctrlKey || event.metaKey ) {
				switch ( String.fromCharCode(event.which).toLowerCase() ) {
					case 's':
						event.preventDefault();
						api.previewer.save();
						break;
				}
			}
		});

		// for each range input add a value preview output
		$('input[type="range"]').each(function () {
			var $clone = $(this).clone();

			$clone
				.attr('type', 'number')
				.attr('class', 'range-value');

			$(this).after($clone);

			$(this).on('input', function () {
				$(this).siblings('.range-value').val($(this).val());
			});
		});

		if ( $('button[data-action="reset_customify"]').length > 0 ) {
			// reset_button
			$(document).on('click', '#customize-control-reset_customify button', function ( ev ) {
				ev.preventDefault();

				var iAgree = confirm('Do you really want to reset to defaults all the fields? Watch out, this will reset all your Customify options and will save them!');

				if ( !iAgree ) {
					return;
				}

				$.each(api.settings.controls, function ( key, ctrl ) {
					var id = key.replace('_control', '');
					var setting = customify_settings.settings[id];

					if ( !_.isUndefined(setting) && !_.isUndefined(setting.default) ) {

						var start_pos = id.indexOf('[') + 1;
						var end_pos = id.indexOf(']', start_pos);

						id = id.substring(start_pos, end_pos);
						api_set_setting_value(id, setting.default);
					}
				});

				api.previewer.save();
			});

			// add a reset button for each panel
			$('.panel-meta').each(function ( el, key ) {
				var container = $(this).parents('.control-panel'),
					id = container.attr('id'),
					panel_id = id.replace('accordion-panel-', '');


				$(this).parent().append('<button class="reset_panel button" data-panel="' + panel_id + '">Panel\'s defaults</button>');
			});

			// reset panel
			$(document).on('click', '.reset_panel', function ( e ) {
				e.preventDefault();

				var panel_id = $(this).data('panel'),
					panel = api.panel(panel_id),
					sections = panel.sections(),
					iAgree = confirm("Do you really want to reset " + panel.params.title + "?");

				if ( !iAgree ) {
					return;
				}
				if ( sections.length > 0 ) {
					$.each(sections, function () {
						//var settings = this.settings();
						var controls = this.controls();

						if ( controls.length > 0 ) {
							$.each(controls, function ( key, ctrl ) {
								var id = ctrl.id.replace('_control', ''),
									setting = customify_settings.settings[id];

								if ( !_.isUndefined(setting) && !_.isUndefined(setting.default) ) {

									var start_pos = id.indexOf('[') + 1,
										end_pos = id.indexOf(']', start_pos);

									id = id.substring(start_pos, end_pos);
									api_set_setting_value(id, setting.default);
								}
							});
						}
					});
				}
			});

			//add reset section
			$('.accordion-section-content').each(function ( el, key ) {
				var section = $(this).parent(),
					section_id = section.attr('id');

				if ( ( ( !_.isUndefined(section_id) ) ? section_id.indexOf(customify_settings.options_name) : -1 ) === -1 ) {
					return;
				}

				if ( !_.isUndefined(section_id) && section_id.indexOf('accordion-section-') > -1 ) {
					var id = section_id.replace('accordion-section-', '');
					$(this).prepend('<button class="reset_section button" data-section="' + id + '">Section\'s defaults</button>');
				}
			});

			// reset section event
			$(document).on('click', '.reset_section', function ( e ) {
				e.preventDefault();

				var section_id = $(this).data('section'),
					section = api.section(section_id),
					controls = section.controls();

				var iAgree = confirm("Do you really want to reset " + section.params.title + "?");

				if ( !iAgree ) {
					return;
				}

				if ( controls.length > 0 ) {
					$.each(controls, function ( key, ctrl ) {
						var id = ctrl.id.replace('_control', ''),
							setting = customify_settings.settings[id];

						if ( !_.isUndefined(setting) && !_.isUndefined(setting.default) ) {

							var start_pos = id.indexOf('[') + 1,
								end_pos = id.indexOf(']', start_pos);

							id = id.substring(start_pos, end_pos);
							api_set_setting_value(id, setting.default);
						}
					});
				}
			});
		}

		$(document).on('change keyup', '.customize-control-range input.range-value', function () {
			var range = $(this).siblings('input[type="range"]');
			range.val($(this).val());
			range.trigger('change');
		});

		$(document).on('change', '.customify_typography_font_subsets', function ( ev ) {

			var $input = $(this).parents('.options').siblings('.customify_typography').children('.customify_typography_values'),
				current_val = $input.val();

			current_val = JSON.parse(decodeURIComponent(current_val));

			//maybe the selected option holds a JSON in its value
			current_val.selected_subsets = maybeJsonParse($(this).val());

			$input.val(encodeURIComponent(JSON.stringify(current_val)));

			$input.trigger('change');
		});

		$(document).on('change', '.customify_typography_font_weight', function ( ev ) {

			var $input = $(this).parents('.options').siblings('.customify_typography').children('.customify_typography_values'),
				current_val = $input.val();

			current_val = maybeJsonParse(current_val);
			// @todo currently the font weight selector works for one value only
			// maybe make this a multiselect

			//maybe the selected option holds a JSON in its value
			current_val.selected_variants = {0: maybeJsonParse($(this).val())};

			$input.val(encodeURIComponent(JSON.stringify(current_val)));
			$input.trigger('change');
		});

		// presets
		$(document).on('change', '.customify_preset.select', function () {
			var this_option = $(this).children('[value="' + $(this).val() + '"]'),
				data = $(this_option).data('options');

			if ( !_.isUndefined(data) ) {
				$.each(data, function ( id, value ) {
					api_set_setting_value(id, value);
				});
			}

			api.previewer.refresh();
		});

		$(document).on('click', '.customify_preset.radio input, .customify_preset.radio_buttons input, .awesome_presets input', function () {
			var this_option = this,//$(this).children('[value="' + $(this).val() + '"]');
				data = $(this_option).data('options');

			if ( !_.isUndefined(data) ) {
				$.each(data, function ( id, value ) {
					api_set_setting_value(id, value);
				});
			}

			api.previewer.refresh();
		});

		// bind our event on click
		$(document).on('click', '.customify_import_demo_data_button', function ( event ) {

			//@todo start an animation here
			var key = $(this).data('key');

			var import_queue = new Queue(api);

			/// calculate the number of steps
			var steps = [];

			if ( !_.isUndefined(customify_settings.settings[key].imports) ) {

				$.each(customify_settings.settings[key].imports, function ( i, import_setts, k ) {
					if ( _.isUndefined(import_setts.steps) ) {
						steps.push({id: i, type: import_setts.type});
					} else {
						var count = import_setts.steps;

						while ( count >= 1 ) {
							steps.push({id: i, type: import_setts.type, count: count});
							count = count - 1;
						}
					}
				});
			}

			import_queue.add_steps('import_demo_data_action_id', steps);
			return false;
		});

		customifyBackgroundJsControl.init();

		// sometimes there may be needed a php save
		if ( getUrlVars('save_customizer_once') ) {
			api.previewer.save();
		}

		setTimeout(function () {
			customifyFoldingFields();
		}, 1000);


		// Handle the section tabs (ex: Layout | Fonts | Colors)
		(function() {
			var $navs = $( '.js-section-navigation' );

			$navs.each( function () {
				var $nav = $( this );
				var $title = $nav.parents( '.accordion-section-content' ).find( '.customize-section-title' );

				$nav.closest('.customize-control').addClass('screen-reader-text');
				$title.append( $nav ).parent().addClass( 'has-nav' );
			});

			$('.js-section-navigation a').on( 'click', function(e) {
				e.preventDefault();

				var $sidebar = $( this ).parents( '.customize-pane-child' );
				var $parent = $(this).parents( '.accordion-section-content' );
				var href = $.attr(this, 'href');

				if ( href != '#' ) {
					$sidebar.animate({
						scrollTop: $( $.attr(this, 'href') ).position().top - $parent.find( '.customize-section-title' ).outerHeight()
					}, 500);
				}
			});
		})();

		(function() {
			// Close a font field when clicking on another field
			$( '.customify_font_tooltip' ).on( 'click', function() {
				if ( $( this ).prop( 'checked' ) === true ) {
					$( '.customify_font_tooltip' ).prop( 'checked', false );
					$( this ).prop( 'checked', true );
				}
			});
		})();
	});

	/**
	 * This function will search for all the interdependend fields and make a bound between them.
	 * So whenever a target is changed, it will take actions to the dependent fields.
	 * @TOOD  this is still written in a barbaric way, refactor when needed
	 */
	var customifyFoldingFields = function () {

		if ( _.isUndefined(customify_settings) || _.isUndefined(customify_settings.settings) ) {
			return; // bail
		}

		/**
		 * Let's iterate through all the customify settings and gather all the fields that have a "show_if"
		 * property set.
		 *
		 * At the end `targets` will hold a list of [ target : [field, field,...], ... ]
		 * so when a target is changed we will change all the fields.
		 */
		var targets = {};

		$.fn.reactor.defaults.compliant = function () {
			$(this).slideDown();
			// $(this).animate({opacity: 1});
			$(this).find(':disabled').attr({disabled: false});
		};

		$.fn.reactor.defaults.uncompliant = function () {
			$(this).slideUp();
			// $(this).animate({opacity: 0.25});
			$(this).find(':enabled').attr({disabled: true});
		};

		var IS = $.extend({}, $.fn.reactor.helpers);

		var bind_folding_events = function ( parent_id, field, relation ) {

			var key = null;

			if ( _.isString(field) ) {
				key = field;
			} else if ( ! _.isUndefined(field.id) ) {
				key = field.id;
			} else if ( isString( field[0] ) ) {
				key = field[0];
			} else {
				return; // no key, no fun
			}

			var value = 1, // by default we use 1 the most used value for checkboxes or inputs
				compare = '==', // ... ye
				action = "show",
				between = [0,1]; // can only be `show` or `hide`

			var target_key = customify_settings.options_name + '[' + key + ']';

			var target_type = customify_settings.settings[target_key].type;

			// we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
			// but we also support a non-associative array like `array( $id, $value, $compare )`
			if ( ! _.isUndefined ( field.value ) ) {
				value = field.value;
			} else if ( ! _.isUndefined( field[1] ) && ! _.isString(field[1]) ) {
				value = field[1];
			}

			if ( ! _.isUndefined(field.compare) ) {
				compare = field.compare;
			} else if ( ! _.isUndefined(field[2]) ) {
				compare = field[2];
			}

			if ( !_.isUndefined(field.action) ) {
				action = field.action;
			} else if ( !_.isUndefined(field[3]) ) {
				action = field[3];
			}

			// a field can also overwrite the parent relation
			if ( !_.isUndefined(field.relation) ) {
				action = field.relation;
			} else if ( !_.isUndefined(field[4]) ) {
				action = field[4];
			}

			if ( !_.isUndefined(field.between) ) {
				between = field.between;
			}

			/**
			 * Now for each target we have, we will bind a change event to hide or show the dependent fields
			 */
			var target_selector = '[data-customize-setting-link="' + customify_settings.options_name + '[' + key + ']"]';

			switch ( target_type ) {
				case 'checkbox':
					$(parent_id).reactIf(target_selector, function () {
						return $(this).is(':checked') == value;
					});
					break;

				case 'radio':
				case 'radio_image':

					// in case of an array of values we use the ( val in array) condition
					if ( _.isObject(value) ) {
						$(parent_id).reactIf(target_selector, function () {
							return ( value.indexOf( $(target_selector + ':checked').val() ) !== -1 );
						});
					} else { // in any other case we use a simple == comparison
						$(parent_id).reactIf(target_selector, function () {
							return $(target_selector + ':checked').val() == value;
						});
					}
					break;

				case 'range':
					var x = IS.Between(between[0], between[1]);

					$(parent_id).reactIf(target_selector, x);
					break;

				default:
					// in case of an array of values we use the ( val in array) condition
					if ( _.isObject(value) ) {
						$(parent_id).reactIf(target_selector, function () {
							return ( value.indexOf($(target_selector).val()) !== -1 );
						});
					} else { // in any other case we use a simple == comparison
						$(parent_id).reactIf(target_selector, function () {
							return $(target_selector).val() == value;
						});
					}
					break;
			}

			$(target_selector).trigger('change');
			$('.reactor').trigger('change.reactor'); // triggers all events on load
		};

		$.each(customify_settings.settings, function ( id, field ) {
			/**
			 * Here we have the id of the fields. but we know for sure that we just need his parent selector
			 * So we just create it
			 */
			var parent_id = id.replace('[', '-');
			parent_id = parent_id.replace(']', '');
			parent_id = '#customize-control-' + parent_id + '_control';

			// get only the fields that have a 'show_if' property
			if ( field.hasOwnProperty('show_if') ) {
				var relation = 'AND';

				if ( ! _.isUndefined( field.show_if.relation ) ) {
					relation = field.show_if.relation;
					// remove the relation property, we need the config to be array based only
					delete field.show_if.relation;
				}

				/**
				 * The 'show_if' can be a simple array with one target like: [ id, value, comparison, action ]
				 * Or it could be an array of multiple targets and we need to process both cases
				 */

				if ( ! _.isUndefined( field.show_if.id ) ) {
					bind_folding_events(parent_id, field.show_if, relation );
				} else if ( _.isObject( field.show_if ) ) {
					$.each(field.show_if, function ( i, j ) {
						bind_folding_events( parent_id, j, relation );
					});
				}
			}
		});
	};

	var get_typography_font_family = function ( $el ) {

		var font_family_value = $el.val();
		// first time this will not be a json so catch that error
		try {
			font_family_value = JSON.parse(font_family_value);
		} catch ( e ) {
			return {font_family: font_family_value};
		}

		if ( !_.isUndefined(font_family_value.font_family) ) {
			return font_family_value.font_family;
		}

		return false;
	};

	// get each typography field and bind events
	var prepare_typography_field = function () {

		var $typos = $('.customify_typography_font_family');

		$typos.each(function () {
			var font_family_select = this,
				$input = $(font_family_select).siblings('.customify_typography_values');
			// on change
			$(font_family_select).on('change', function () {
				update_siblings_selects(font_family_select);
				$input.trigger('change');
			});
			update_siblings_selects(font_family_select);
		});
	};

	var api_set_setting_value = function ( id, value ) {

		var setting_id = customify_settings.options_name + '[' + id + ']',
			setting = api(setting_id),
			field = $('[data-customize-setting-link="' + setting_id + '"]'),
			field_class = $(field).parent().attr('class');

		if ( !_.isUndefined(field_class) && field_class === 'customify_typography' ) {

			var family_select = field.siblings('select');

			if ( _.isString(value) ) {
				var this_option = family_select.find('option[value="' + value + '"]');
				$(this_option[0]).attr('selected', 'selected');
				update_siblings_selects(family_select);
			} else if ( _.isObject(value) ) {
				var this_family_option = family_select.find('option[value="' + value['font_family'] + '"]');
				$(this_family_option[0]).attr('selected', 'selected');

				update_siblings_selects(this_family_option);

				setTimeout(function () {
					var weight_select = field.parent().siblings('.options').find('.customify_typography_font_weight');

					var this_weight_option = weight_select.find('option[value="' + value['selected_variants'] + '"]');

					$(this_weight_option[0]).attr('selected', 'selected');

					update_siblings_selects(this_family_option);

					weight_select.trigger('change');
				}, 300);
			}

			family_select.trigger('change');

		} else if(  !_.isUndefined(field_class) && field_class === 'font-options__wrapper' ) {

			// if the values is a simple string it should be the font family
			if ( _.isString( value ) ) {

				var option = field.parent().find('option[value="' + value + '"]');

				option.attr('selected', 'selected');
				// option.parents('select').trigger('change');
			} else if (  _.isObject(value) ) {
				// @todo process each font property
			}

		} else {
			setting.set(value);
		}
	};

	var update_siblings_selects = function ( font_select ) {
		var selected_font = $(font_select).val(),
			$input = $(font_select).siblings('.customify_typography_values'),
			current_val = $input.attr('value');

		if ( current_val === '[object Object]' ) {
			current_val = $input.data('default');
		} else if ( _.isString(current_val) && !isJsonString(current_val) && current_val.substr(0, 1) == '[' ) {
			// a rare case when the value isn't a json but is a representative string like [family,weight]
			current_val = current_val.split(',');
			var new_current_value = {};
			if ( !_.isUndefined(current_val[0]) ) {
				new_current_value['font_family'] = current_val[0];
			}

			if ( !_.isUndefined(current_val[1]) ) {
				new_current_value['selected_variants'] = current_val[1];
			}

			current_val = JSON.stringify(new_current_value);
		}

		var $font_weight = $(font_select).parent().siblings('ul.options').find('.customify_typography_font_weight'),
			$font_subsets = $(font_select).parent().siblings('ul.options').find('.customify_typography_font_subsets');

		try {
			current_val = JSON.parse(decodeURIComponent(current_val));
		} catch ( e ) {

			// in case of an error, force the rebuild of the json
			if ( _.isUndefined($(font_select).data('bound_once')) ) {

				$(font_select).data('bound_once', true);

				$(font_select).change();
				$font_weight.change();
				$font_subsets.change();
			}
		}

		// first try to get the font from sure sources, not from the recommended list.
		var option_data = $(font_select).find(':not(optgroup[label=Recommended]) option[value="' + selected_font + '"]');
		// however, if there isn't an option found, get what you can
		if ( option_data.length < 1 ) {
			option_data = $(font_select).find('option[value="' + selected_font + '"]');
		}

		if ( option_data.length > 0 ) {

			var font_type = option_data.data('type'),
				value_to_add = {'type': font_type, 'font_family': selected_font},
				variants = null,
				subsets = null;

			if ( font_type == 'std' ) {
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
				};
				if ( !_.isUndefined($(option_data[0]).data('variants')) ) {
					//maybe the variants are a JSON
					variants = maybeJsonParse($(option_data[0]).data('variants'));
				}
			} else {
				//maybe the variants are a JSON
				variants = maybeJsonParse($(option_data[0]).data('variants'));

				//maybe the subsets are a JSON
				subsets = maybeJsonParse($(option_data[0]).data('subsets'));
			}

			// make the variants selector
			if ( !_.isUndefined(variants) && !_.isNull(variants) && !_.isEmpty(variants) ) {

				value_to_add['variants'] = variants;
				// when a font is selected force the first weight to load
				value_to_add['selected_variants'] = {0: variants[0]};

				var variants_options = '',
					count_weights = 0;

				if ( _.isArray(variants) || _.isObject(variants) ) {
					// Take each variant and produce the option markup
					$.each(variants, function ( key, el ) {
						var is_selected = '';
						if ( _.isObject(current_val.selected_variants) && inObject(el, current_val.selected_variants) ) {
							is_selected = ' selected="selected"';
						} else if ( _.isString(current_val.selected_variants) && el === current_val.selected_variants ) {
							is_selected = ' selected="selected"';
						}

						// initialize
						var variant_option_value = el,
							variant_option_display = el;

						// If we are dealing with a object variant then it means things get tricky (probably it's our fault but bear with us)
						// This probably comes from our Fonto plugin - a font with individually named variants - hence each has its own font-family
						if ( _.isObject(el) ) {
							//put the entire object in the variation value - we will need it when outputting the custom CSS
							variant_option_value = encodeURIComponent(JSON.stringify(el));
							variant_option_display = '';

							//if we have weight and style then "compose" them into something standard
							if ( !_.isUndefined(el['font-weight']) ) {
								variant_option_display += el['font-weight'];
							}

							if ( _.isString(el['font-style']) && $.inArray(el['font-style'].toLowerCase(), ["normal", "regular"]) < 0 ) { //this comparison means it hasn't been found
								variant_option_display += el['font-style'];
							}
						}

						variants_options += '<option value="' + variant_option_value + '"' + is_selected + '>' + variant_option_display + '</option>';
						count_weights++;
					});
				}

				if ( !_.isUndefined($font_weight) ) {
					$font_weight.html(variants_options);
					// if there is no weight or just 1 we hide the weight select ... cuz is useless
					if ( $(font_select).data('load_all_weights') === true || count_weights <= 1 ) {
						$font_weight.parent().css('display', 'none');
					} else {
						$font_weight.parent().css('display', 'inline-block');
					}
				}
			} else if ( !_.isUndefined($font_weight) ) {
				$font_weight.parent().css('display', 'none');
			}

			// make the subsets selector
			if ( !_.isUndefined(subsets) && !_.isNull(subsets) && !_.isEmpty(subsets) ) {

				value_to_add['subsets'] = subsets;
				// when a font is selected force the first subset to load
				value_to_add['selected_subsets'] = {0: subsets[0]};
				var subsets_options = '',
					count_subsets = 0;
				$.each(subsets, function ( key, el ) {
					var is_selected = '';
					if ( _.isObject(current_val.selected_subsets) && inObject(el, current_val.selected_subsets) ) {
						is_selected = ' selected="selected"';
					}

					subsets_options += '<option value="' + el + '"' + is_selected + '>' + el + '</option>';
					count_subsets++;
				});

				if ( !_.isUndefined($font_subsets) ) {
					$font_subsets.html(subsets_options);

					// if there is no subset or just 1 we hide the subsets select ... cuz is useless
					if ( count_subsets <= 1 ) {
						$font_subsets.parent().css('display', 'none');
					} else {
						$font_subsets.parent().css('display', 'inline-block');
					}
				}
			} else if ( !_.isUndefined($font_subsets) ) {
				$font_subsets.parent().css('display', 'none');
			}

			$input.val(encodeURIComponent(JSON.stringify(value_to_add)));
		}
	};

	/** Modules **/

	var customifyBackgroundJsControl = (function () {
		"use strict";

		function init() {
			// Remove the image button
			$('.customize-control-custom_background .remove-image, .customize-control-custom_background .remove-file').unbind('click').on('click', function ( e ) {
				removeImage($(this).parents('.customize-control-custom_background:first'));
				preview($(this));
				return false;
			});

			// Upload media button
			$('.customize-control-custom_background .background_upload_button').unbind().on('click', function ( event ) {
				addImage(event, $(this).parents('.customize-control-custom_background:first'));
			});

			$('.customify_background_select').on('change', function () {
				preview($(this));
			});
		}

		// Add a file via the wp.media function
		function addImage( event, selector ) {

			event.preventDefault();

			var frame;
			var jQueryel = jQuery(this);

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
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
			});

			// When an image is selected, run a callback.
			frame.on('select', function () {
				// Grab the selected attachment.
				var attachment = frame.state().get('selection').first();
				frame.close();

				if ( attachment.attributes.type !== "image" ) {
					return;
				}

				selector.find('.upload').attr('value', attachment.attributes.url);
				selector.find('.upload-id').attr('value', attachment.attributes.id);
				selector.find('.upload-height').attr('value', attachment.attributes.height);
				selector.find('.upload-width').attr('value', attachment.attributes.width);

				var thumbSrc = attachment.attributes.url;
				if ( !_.isUndefined(attachment.attributes.sizes) && !_.isUndefined(attachment.attributes.sizes.thumbnail) ) {
					thumbSrc = attachment.attributes.sizes.thumbnail.url;
				} else if ( !_.isUndefined(attachment.attributes.sizes) ) {
					var height = attachment.attributes.height;
					for ( var key in attachment.attributes.sizes ) {
						var object = attachment.attributes.sizes[key];
						if ( object.height < height ) {
							height = object.height;
							thumbSrc = object.url;
						}
					}
				} else {
					thumbSrc = attachment.attributes.icon;
				}

				selector.find('.customify_background_input.background-image').val(attachment.attributes.url);

				if ( !selector.find('.upload').hasClass('noPreview') ) {
					selector.find('.preview_screenshot').empty().hide().append('<img class="preview_image" src="' + thumbSrc + '">').slideDown('fast');
				}
				//selector.find('.media_upload_button').unbind();
				selector.find('.remove-image').removeClass('hide');//show "Remove" button
				selector.find('.customify_background_select').removeClass('hide');//show "Remove" button

				preview(selector);
			});

			// Finally, open the modal.
			frame.open();
		}

		// Update the background preview
		function preview( selector ) {

			var $parent = selector.parents('.customize-control-custom_background:first');

			if ( selector.hasClass('customize-control-custom_background') ) {
				$parent = selector;
			}

			if ( $parent.length > 0 ) {
				$parent = $($parent[0]);
			} else {
				return;
			}

			var image_holder = $parent.find('.background-preview');

			if ( !image_holder ) { // No preview present
				return;
			}

			var the_id = $parent.find('.button.background_upload_button').data('setting_id'),
				this_setting = api.instance(the_id);

			var background_data = {};

			$parent.find('.customify_background_select, .customify_background_input').each(function () {
				var data = $(this).serializeArray();

				data = data[0];
				if ( data && data.name.indexOf('[background-') != -1 ) {

					background_data[$(this).data('select_name')] = data.value;

					//default_default[data.name] = data.value;
					//if (data.name == "background-image") {
					//	css += data.name + ':url("' + data.value + '");';
					//} else {
					//	css += data.name + ':' + data.value + ';';
					//}
				}
			});

			api.instance(the_id).set(background_data);
			//// Notify the customizer api about this change
			api.trigger('change');
			api.previewer.refresh();

			//image_holder.attr('style', css).fadeIn();
		}

		// Update the background preview
		function removeImage( parent ) {
			var selector = parent.find('.upload_button_div');
			// This shouldn't have been run...
			if ( !selector.find('.remove-image').addClass('hide') ) {
				return;
			}

			selector.find('.remove-image').addClass('hide');//hide "Remove" button
			parent.find('.customify_background_select').addClass('hide');

			selector.find('.upload').val('');
			selector.find('.upload-id').val('');
			selector.find('.upload-height').val('');
			selector.find('.upload-width').val('');
			parent.find('.customify_background_input.background-image').val('');

			var customizer_id = selector.find('.background_upload_button').data('setting_id'),
				this_setting = api.control(customizer_id + '_control'),
				current_vals = this_setting.setting(),
				screenshot = parent.find('.preview_screenshot'),
				to_array = $.map(current_vals, function ( value, index ) {
					return [value];
				});

			// Hide the screenshot
			screenshot.slideUp();
			selector.find('.remove-file').unbind();
			to_array['background-image'] = '';
			this_setting.setting(to_array);
		}

		return {
			init: init
		}
	})(jQuery);

	var customifyFontSelect = (function () {
		var fontSelector = '.customify_font_family',
			selectPlacehoder = "Select a font family",
			weightPlaceholder = "Select a font weight",
			subsetPlaceholder = "Select a font subset";

		function init( wpapi ) {
				$(fontSelector).select2({
					placeholder: selectPlacehoder
				}).on('change', function ( e ) {
					var new_option = $(e.target).find('option:selected'),
						wraper = $(e.target).closest('.font-options__wrapper'),
						type = $(new_option).data('type');

					update_weight_field(new_option, wraper);

					update_subset_field(new_option, wraper);

					// serialize stuff and refresh
					update_font_value(wraper);
				});

			$('.customify_font_weight').each(function ( i, el  ) {

				var select2_args = {
					debug: false
				};

				// all this fuss is for the case when the font doesn't come with variants from PHP, lile a theme_font
				if ( this.options.length === 0 ) {
					var wraper = $(el).closest('.font-options__wrapper'),
						font = wraper.find('.customify_font_family'),
						option = font[0].options[font[0].selectedIndex],
						variants =  maybeJsonParse( $(option).data('variants') ),
						data = [],
						selecter_variants = $(el).data('default') || null;

					if ( typeof variants === "undefined" ) {
						$(this).hide();
						return;
					}

					$.each( variants, function ( index, weight ) {
						var this_value = {
							id: weight,
							text: weight
						};

						if ( selecter_variants !== null && weight == selecter_variants ) {
							this_value.selected = true;
						}

						data.push(this_value);
					} );

					if ( data !== [] ) {
						select2_args.data = data;
					}
				}

				$(this).select2( select2_args )
				.on('change', function ( e ) {
					var wraper = $(e.target).closest('.font-options__wrapper');
					var current_value = update_font_value(wraper);
					// temporary just set the new value and refresh the previewr
					// we may update this with a live version sometime
					var value_holder = wraper.children('.customify_font_values');
					var setting_id = $(value_holder).data('customize-setting-link');
					var setting = wp.customize(setting_id);
					setting.set(encodeValues(current_value));
				});
			});

			$('.customify_font_subsets')
				.select2({
					placeholder: "Extra Subsets"
				})
				.on('change', function ( e ) {
					var wraper = $(e.target).closest('.font-options__wrapper');
					var current_value = update_font_value(wraper);
					// temporary just set the new value and refresh the previewr
					// we may update this with a live version sometime
					var value_holder = wraper.children('.customify_font_values');
					var setting_id = $(value_holder).data('customize-setting-link');
					var setting = wp.customize(setting_id);
					setting.set(encodeValues(current_value));
				});

			var rangers = $(fontSelector).parents('.font-options__wrapper').find('input[type=range]');
			var selects = $(fontSelector).parents('.font-options__wrapper').find('select');

			if ( selects.length > 0 ) {
				selects.on('change', function ( e ) {
					var wraper = $(e.target).closest('.font-options__wrapper');
					var current_value = update_font_value(wraper);
					// temporary just set the new value and refresh the previewr
					// we may update this with a live version sometime
					var value_holder = wraper.children('.customify_font_values');
					var setting_id = $(value_holder).data('customize-setting-link');
					var setting = wp.customize(setting_id);
					setting.set(encodeValues(current_value));
				});
			}

			if ( rangers.length > 0 ) {
				rangers.on('mousemove', function ( e ) {
					var wraper = $(e.target).closest('.font-options__wrapper');
					var current_value = update_font_value(wraper);
					// temporary just set the new value and refresh the previewr
					// we may update this with a live version sometime
					var value_holder = wraper.children('.customify_font_values');
					var setting_id = $(value_holder).data('customize-setting-link');
					var setting = wp.customize(setting_id);
					setting.set(encodeValues(current_value));

					wp.customize.previewer.send( 'font-changed' );
				});
			}

			var self = this;

			wp.customize.previewer.bind( 'ready', function () {
				self.render_fonts();
			});
		}

		/**
		 * This function updates the data in font weight selector from the givin <option> element
		 *
		 * @param new_option
		 * @param wraper
		 */
		function update_weight_field( option, wraper ) {
			var variants = $(option).data('variants'),
				font_weights = wraper.find('.customify_font_weight'),
				selected_variant = font_weights.data('default'),
				new_variants = [],
				type =  $(option).data('type'),
				id = wraper.find('.customify_font_values').data('customizeSettingLink');

			variants = maybeJsonParse(variants);

			if ( customify_settings.settings[id].load_all_weights || typeof variants === "undefined" || Object.keys(variants).length < 2 ) {
				font_weights.parent().hide();
			} else {
				font_weights.parent().show();
			}

			// we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
			$.each(variants, function ( i, j ) {
				new_variants[i] = {
					'id': j,
					'text': j
				};

				if ( selected_variant == j ) {
					new_variants[i].selected = true;
				}
			});

			// we need to clear the old values
			$(font_weights).select2().empty();
			$(font_weights).select2({
				data: new_variants
			}).on('change', function ( e ) {
				var select_element = e.target;
				var wraper = $(select_element).closest('.font-options__wrapper');
				update_font_value(wraper);
			});
		}

		/**
		 *  This function updates the data in font subset selector from the givin <option> element
		 * @param new_option
		 * @param wraper
		 */
		function update_subset_field( option, wraper ) {
			var subsets = $(option).data('subsets'),
				font_subsets = wraper.find('.customify_font_subsets'),
				selected_subsets = font_subsets.data('default'),
				new_subsets = [],
				type =  $(option).data('type');

			if ( type !== 'google' ) {
				font_subsets.parent().hide();
				return;
			}

			var current_value = wraper.children('.customify_font_values').val();

			current_value = maybeJsonParse( current_value );
			current_value = current_value.selected_subsets;

			subsets = maybeJsonParse( subsets );

			if ( Object.keys(subsets).length < 2 ) {
				font_subsets.parent().hide();
			} else {
				font_subsets.parent().show();
			}

			// we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
			$.each(subsets, function ( i, j ) {
				new_subsets[i] = {
					'id': j,
					'text': j
				};

				// current_subsets
				if ( typeof current_value !== 'undefined' && current_value !== null && current_value.indexOf( j ) !== -1 ) {
					new_subsets[i].selected = true;
				}
			});

			// we need to clear the old values
			$(font_subsets).select2().empty();
			$(font_subsets).select2({
				data: new_subsets
			}).on('change', function ( e ) {
				var select_element = e.target;
				var wraper = $(select_element).closest('.font-options__wrapper');
				update_font_value(wraper);
			});
		}

		/**
		 * This function is a custom value serializer for our entire font field
		 * It collects values and saves them (encoded) into the `.customify_font_values` input's value
		 */
		function update_font_value( wraper ) {
			var element = $(wraper).find('.font-options__wrapper'),
				options_list = $(wraper).find('.font-options__options-list'),
				inputs = options_list.find('select, input'),
				value_holder = wraper.children('.customify_font_values'),
				new_vals = {};

			inputs.each(function ( key, el ) {
				var field = $(el).data('field'),
					value = $(el).val();

				if ( field === 'font_family' ) {
					// the font family also holds the type
					var selected_opt = $(el.options[el.selectedIndex]),
						type = selected_opt.data('type'),
						subsets = selected_opt.data('subsets'),
						variants = selected_opt.data('variants');

					if ( typeof type !== "undefined") {
						new_vals['type'] = type;
						if ( type === 'theme_font' ) {
							new_vals['src'] = selected_opt.data('src');
						}
 					}

					if ( typeof variants !== "undefined") {
						new_vals['variants'] = maybeJsonParse(variants);
					}

					if ( typeof subsets !== "subsets") {
						new_vals['subsets'] = maybeJsonParse(subsets);
					}
				}


				if ( typeof field !== "undefined" && typeof value !== "undefined" && value !== "" ) {
					new_vals[field] = value;
				}
			});

			value_holder.val(encodeValues(new_vals));

			return new_vals;
		}

		var maybeJsonParse = function ( value ) {
			var parsed;

			//try and parse it, with decodeURIComponent
			try {
				parsed = JSON.parse(decodeURIComponent(value));
			} catch ( e ) {

				// in case of an error, treat is as a string
				parsed = value;
			}

			return parsed;
		};

		function encodeValues( obj ) {
			return encodeURIComponent(JSON.stringify(obj));
		}

		function render_fonts() {
			$( '.customify_font_family').select2().trigger('change')
		}

		return {
			render_fonts: render_fonts,
			init: init,
			update_font_value: update_font_value
		};
	})();

	var Queue = function () {
		var lastPromise = null;
		var queueDeferred = null;
		var methodDeferred = null;

		this.add_steps = function ( key, steps, args ) {
			var self = this;
			this.methodDeferred = $.Deferred();
			this.queueDeferred = this.setup();

			$.each(steps, function ( i, step ) {
				self.queue(key, step);
			});
		};

		this.process_remote_step = function ( key, data, step ) {
			var self = this;

			if ( _.isUndefined(data) || _.isNull(data) ) {
				return false;
			}

			var new_step = step;
			$.each(data, function ( i, k ) {
				debugger;
				// prepare data for new requests
				new_step.recall_data = k.data;
				new_step.recall_type = k.type;
				new_step.type = 'recall';

				self.queue(key, new_step, k.id);
			});
		};

		this.log_action = function ( action, key, msg ) {
			if ( action === 'start' ) {
				$('.wpGrade-import-results').show();
				$('.wpGrade-import-results').append('<span class="import_step_note imports_step_' + key + '" ><span class="step_info" data-balloon="Working on it" data-balloon-pos="up"></span>Importing ' + key + '</span>');
			} else if ( action === 'end' ) {
				var $notice = $('.imports_step_' + key + ' .step_info');

				if ( $notice.length > 0 || msg !== "undefined" ) {
					$notice.attr('data-balloon', msg);
					$notice.addClass('success');
				} else {
					$notice.attr('data-balloon', 'Done');
					$notice.addClass('failed');
				}
			}
		};

		this.queue = function ( key, data, step_key ) {
			var self = this;
			if ( !_.isUndefined(step_key) ) {
				this.log_action('start', step_key);
			}

			// execute next queue method
			this.queueDeferred.done(this.request(key, data, step_key));
			lastPromise = self.methodDeferred.promise();
		};

		this.request = function ( key, step, step_key ) {
			var self = this;
			// call actual method and wrap output in deferred
			//setTimeout( function() {
			var data_args = {
				action: 'customify_import_step',
				step_id: step.id,
				step_type: step.type,
				option_key: key
			};

			if ( !_.isUndefined(step.recall_data) ) {
				data_args.recall_data = step.recall_data;
			}

			if ( !_.isUndefined(step.recall_type) ) {
				data_args.recall_type = step.recall_type;
			}

			$.ajax({
				url: customify_settings.import_rest_url + 'customify/1.0/import',
				method: 'POST',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
				},
				dataType: 'json',
				contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
				data: data_args
			}).done(function ( response ) {
				if ( !_.isUndefined(response.success) && response.success ) {
					var results = response.data;
					if ( step.type === 'remote' ) {
						self.process_remote_step(key, results, step);
					}
				}

				if ( !_.isUndefined(step_key) && !_.isUndefined(response.message) ) {
					self.log_action('end', step_key, response.message);
				}
			});

			self.methodDeferred.resolve();
			//}, 3450 );
		};

		this.setup = function () {
			var self = this;

			self.queueDeferred = $.Deferred();

			// when the previous method returns, resolve this one
			$.when(lastPromise).always(function () {
				self.queueDeferred.resolve();
			});

			return self.queueDeferred.promise();
		}
	};

	/** HELPERS **/

	/**
	 * Function to check if a value exists in an object
	 * @param value
	 * @param obj
	 * @returns {boolean}
	 */
	var inObject = function ( value, obj ) {
		for ( var k in obj ) {
			if ( !obj.hasOwnProperty(k) ) continue;
			if ( _.isEqual(obj[k], value) ) {
				return true;
			}
		}
		return false;
	};

	var maybeJsonParse = function ( value ) {
		var parsed;

		//try and parse it, with decodeURIComponent
		try {
			parsed = JSON.parse(decodeURIComponent(value));
		} catch ( e ) {

			// in case of an error, treat is as a string
			parsed = value;
		}

		return parsed;
	};

	var getUrlVars = function ( name ) {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for ( var i = 0; i < hashes.length; i++ ) {
			hash = hashes[i].split('=');

			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}

		if ( !_.isUndefined(vars[name]) ) {
			return vars[name];
		}
		return false;
	};

	var isJsonString = function ( str ) {
		try {
			JSON.parse(str);
		} catch ( e ) {
			return false;
		}
		return true;
	};
})(jQuery, window, wp);
