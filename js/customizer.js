(function( $, exports ) {
	$( document ).ready( function() {
		// when the customizer is ready prepare our fields events
		wp.customize.bind( 'ready', function() {
			var api = this,
				timeout = null;

			// add ace editors
			$('.customify_ace_editor' ).each(function( key, el){
				var id = $( this ).attr('id' ),
					css_editor = ace.edit( id );

				var editor_type = $( this ).data('editor_type');
				// init the ace editor
				css_editor.setTheme("ace/theme/github");
				css_editor.getSession().setMode("ace/mode/" + editor_type);

				// hide the textarea and enable the ace editor
				var textarea = $('#' + id + '_textarea').hide();
				css_editor.getSession().setValue(textarea.val());

				// each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
				// if the user types faster than this delay then reset it
				css_editor.getSession().on('change', function(e) {
					if ( timeout !== null ){
						clearTimeout(timeout);
						timeout = null;
					} else {
						timeout = setTimeout( function(){
							//var state = css_editor.session.getState();
							textarea.val(css_editor.getSession().getValue());
							textarea.trigger('change');
						},1500);
					}
				});
			});

			// simple select2 field
			$( '.customify_select2' ).select2();

			prepare_typography_field();

			/**
			 * Make the customizer save on CMD/CTRL+S action
			 * This is awesome!!!
			 */
			$( window ).bind( 'keydown', function( event ) {
				if ( event.ctrlKey || event.metaKey ) {
					switch ( String.fromCharCode( event.which ).toLowerCase() ) {
						case 's':
							event.preventDefault();
							api.previewer.save();
							break;
					}
				}
			} );

			// for each range input add a value preview output
			$( 'input[type="range"]' ).each( function() {
				var $clone = $( this ).clone();

				$clone
					.attr( 'type', 'number' )
					.attr( 'class', 'range-value' );

				$( this ).after( $clone );

				$( this ).on( 'input', function() {
					$( this ).siblings( '.range-value' ).val( $( this ).val() );
				} );
			} );

			if ( $('button[data-action="reset_customify"]' ).length > 0 ) {
				// reset_button
				$( document ).on( 'click', '#customize-control-reset_customify button', function( ev ) {
					ev.preventDefault();

					var iAgree = confirm( 'Do you really want to reset to defaults all the fields? Watch out, this will reset all your Customify options and will save them!' );

					if ( ! iAgree ) {
						return;
					}

					$.each( api.settings.controls, function( key, ctrl ) {
						var id = key.replace( '_control', '' );
						var setting = customify_settings.settings[id];

						if ( typeof setting !== "undefined" && typeof setting.default !== "undefined" ) {

							var start_pos = id.indexOf( '[' ) + 1;
							var end_pos = id.indexOf( ']', start_pos );

							id = id.substring( start_pos, end_pos );
							api_set_setting_value( id, setting.default );
						}
					} );

					api.previewer.save();
				} );

				// add a reset button for each panel
				$( '.panel-meta' ).each( function( el, key ) {
					var container = $( this ).parents( '.control-panel' ),
						id = container.attr( 'id' ),
						panel_id = id.replace( 'accordion-panel-', '' );


					$( this ).parent().append( '<button class="reset_panel button" data-panel="' + panel_id + '">Panel\'s defaults</button>' );
				} );

				// reset panel
				$( document ).on( 'click', '.reset_panel', function( e ) {
					e.preventDefault();

					var panel_id = $( this ).data( 'panel' ),
						panel = api.panel( panel_id ),
						sections = panel.sections(),
						iAgree = confirm( "Do you really want to reset " + panel.params.title + "?" );

					if ( !iAgree ) {
						return;
					}
					if ( sections.length > 0 ) {
						$.each( sections, function() {
							//var settings = this.settings();
							var controls = this.controls();

							if ( controls.length > 0 ) {
								$.each( controls, function( key, ctrl ) {
									var id = ctrl.id.replace( '_control', '' ),
										setting = customify_settings.settings[id];

									if ( typeof setting !== "undefined" && typeof setting.default !== "undefined" ) {

										var start_pos = id.indexOf( '[' ) + 1,
											end_pos = id.indexOf( ']', start_pos );

										id = id.substring( start_pos, end_pos );
										api_set_setting_value( id, setting.default );
									}
								} );
							}
						} );
					}
				} );

				//add reset section
				$( '.accordion-section-content' ).each( function( el, key ) {
					var section = $( this ).parent(),
						section_id = section.attr( 'id' );

					if ( (typeof section_id !== "undefined" ? section_id.indexOf(customify_settings.options_name ) : -1 ) === -1) {
						return;
					}

					if ( typeof section_id !== 'undefined' && section_id.indexOf( 'accordion-section-' ) > -1 ) {
						var id = section_id.replace( 'accordion-section-', '' );
						$( this ).prepend( '<button class="reset_section button" data-section="' + id + '">Section\'s defaults</button>' );
					}
				} );

				// reset section event
				$( document ).on( 'click', '.reset_section', function( e ) {
					e.preventDefault();

					var section_id = $( this ).data( 'section' ),
						section = api.section( section_id ),
						controls = section.controls();

					var iAgree = confirm( "Do you really want to reset " + section.params.title + "?" );

					if ( !iAgree ) {
						return;
					}

					if ( controls.length > 0 ) {
						$.each( controls, function( key, ctrl ) {
							var id = ctrl.id.replace( '_control', '' ),
								setting = customify_settings.settings[id];

							if ( typeof setting !== "undefined" && typeof setting.default !== "undefined" ) {

								var start_pos = id.indexOf( '[' ) + 1,
									end_pos = id.indexOf( ']', start_pos );

								id = id.substring( start_pos, end_pos );
								api_set_setting_value( id, setting.default );
							}
						} );
					}
				} );
			}

			$( document ).on( 'change', '.customize-control input.range-value', function() {
				var range = $( this ).siblings( 'input[type="range"]' );
				range.val( $( this ).val() );
			} );

			$( document ).on( 'change', '.customify_typography_font_subsets', function( ev ) {

				var $input = $( this ).parents( '.options' ).siblings( '.customify_typography' ).children( '.customify_typography_values' ),
					current_val = $input.val();

				current_val = JSON.parse( current_val );
				current_val.selected_subsets = $( this ).val();

				$input.val( JSON.stringify( current_val ) );

				$input.trigger( 'change' );
			} );

			$( document ).on( 'change', '.customify_typography_font_weight', function( ev ) {

				var $input = $( this ).parents( '.options' ).siblings( '.customify_typography' ).children( '.customify_typography_values' ),
					current_val = $input.val();

				current_val = JSON.parse( current_val );
				// @todo currently the font weight selector works for one value only
				// maybe make this a multiselect
				current_val.selected_variants = {0: $( this ).val()};

				$input.val( JSON.stringify( current_val ) );
				$input.trigger( 'change' );
			} );

			// presets
			$( document ).on( 'change', '.customify_preset.select', function() {
				var api = wp.customize,
					this_option = $( this ).children( '[value="' + $( this ).val() + '"]' ),
					data = $( this_option ).data( 'options' );

				if ( typeof data !== 'undefined' ) {
					$.each( data, function( id, value ) {
						api_set_setting_value( id, value );
					} );
				}

				api.previewer.refresh();
			} );

			$( document ).on( 'click', '.customify_preset.radio input, .customify_preset.radio_buttons input, .awesome_presets input', function() {
				var api = wp.customize;
				var this_option = this;//$(this).children('[value="' + $(this).val() + '"]');
				var data = $( this_option ).data( 'options' );

				if ( typeof data !== 'undefined' ) {
					$.each( data, function( id, value ) {
						api_set_setting_value( id, value );
					} );
				}

				api.previewer.refresh();
			} );

			customifyBackgroundJsControl.init();

			// sometimes there may be needed a php save
			if ( getUrlVars('save_customizer_once') ) {
				api.previewer.save();
			}
		} );

		var get_typography_font_family = function( $el ) {

			var font_family_value = $el.val();
			// first time this will not be a json so catch that error
			try {
				font_family_value = JSON.parse( font_family_value );
			} catch ( e ) {
				return {font_family: font_family_value};
			}

			if ( typeof font_family_value.font_family !== 'undefined' ) {
				return font_family_value.font_family;
			}

			return false;
		};

		// get each typography field and bind events
		var prepare_typography_field = function() {

			var $typos = $( '.customify_typography_font_family' );

			$typos.each( function() {
				var font_family_select = this,
					$input = $( font_family_select ).siblings( '.customify_typography_values' );
				// on change
				$( font_family_select ).on( 'change', function() {
					update_siblings_selects( font_family_select );
					$input.trigger( 'change' );
				} );
				update_siblings_selects( font_family_select );
			} );
		};

		var api_set_setting_value = function( id, value ) {

			var api = wp.customize,
				setting_id = customify_settings.options_name + '[' + id + ']',
				setting = api( setting_id ),
				field = $( '[data-customize-setting-link="' + setting_id + '"]' ),
				field_class = $( field ).parent().attr( 'class' );

			if ( typeof field_class !== "undefined" && field_class === 'customify_typography' ) {

				var family_select = field.siblings( 'select' );

				if ( typeof value === 'string' ) {
					var this_option = family_select.find( 'option[value="' + value + '"]' );
					$( this_option[0] ).attr( 'selected', 'selected' );
					update_siblings_selects( family_select );
				} else if ( typeof value === 'object' ) {
					var this_family_option = family_select.find( 'option[value="' + value['font_family'] + '"]' );
					$( this_family_option[0] ).attr( 'selected', 'selected' );

					update_siblings_selects( this_family_option );

					setTimeout(function(){
						var weight_select = field.parent().siblings( '.options' ).find('.customify_typography_font_weight');

						var this_weight_option = weight_select.find( 'option[value="' + value['selected_variants'] + '"]' );

						$( this_weight_option[0] ).attr( 'selected', 'selected' );

						update_siblings_selects( this_family_option );

						weight_select.trigger( 'change' );
					},300);
				}

				family_select.trigger( 'change' );

			} else {
				setting.set( value );
			}
		};

		var update_siblings_selects = function( font_select ) {

			this.bound_once = false;
			var selected_font = $( font_select ).val(),
				$input = $( font_select ).siblings( '.customify_typography_values' ),
				current_val = $input.attr('value');

			if( current_val === '[object Object]' ) {
				current_val = $input.data('default');
			} else if ( typeof current_val === "string" && ! IsJsonString( current_val ) ) {
				// a rare case when the value isn't a json but is a representative string like [family,weight]
				current_val = current_val.split( ',');
				var new_current_value = {};
				if ( typeof current_val[0] !== "undefined" ) {
					new_current_value['font_family'] = current_val[0];
				}

				if ( typeof current_val[1] !== "undefined" ) {
					new_current_value['selected_variants'] = current_val[1];
				}

				current_val = JSON.stringify( new_current_value );
			}

			var $font_weight = $( font_select ).parent().siblings( 'ul.options' ).find( '.customify_typography_font_weight' ),
				$font_subsets = $( font_select ).parent().siblings( 'ul.options' ).find( '.customify_typography_font_subsets' );

			try {
				current_val = JSON.parse( current_val );
			} catch ( e ) {

				// in case of an error, force the rebuild of the json
				if ( typeof $( font_select ).data( 'bound_once' ) === "undefined" ) {

					$( font_select ).data( 'bound_once', true );
					//var api = wp.customize;
					//api.previewer.refresh();

					$( font_select ).change();
					$font_weight.change();
					$font_subsets.change();
				}
			}
			// first try to get the font from sure sources, not from the recommended list.
			var option_data = $( font_select ).find( ':not(optgroup[label=Recommended]) option[value="' + selected_font + '"]' );
			// howevah, if there isn't an option found, get what you can
			if ( option_data.length < 1 ) {
				option_data = $( font_select ).find( 'option[value="' + selected_font + '"]' );
			}

			if ( option_data.length > 0 ) {

				var font_type = option_data.data( 'type' ),
					value_to_add = {'type': font_type, 'font_family': selected_font},
					variants = null,
					subsets = null;

				if ( font_type == 'std' ) {
					variants = {0: '100', 1: '200', 3: '300', 4: '400', 5: '500'};
					if ( typeof $( option_data[0] ).data( 'variants' ) !== 'undefined' ) {
						variants = $( option_data[0] ).data( 'variants' );
					}

				} else {
					variants = $( option_data[0] ).data( 'variants' );
					subsets = $( option_data[0] ).data( 'subsets' );
				}

				// make the variants selector
				if ( typeof variants !== 'undefined' && variants !== null && variants !== '' ) {

					value_to_add['variants'] = variants;
					// when a font is selected force the first weight to load
					value_to_add['selected_variants'] = {0: variants[0]};

					var variants_options = '',
						count_weights = 0;

					$.each( variants, function( key, el ) {
						var is_selected = '';
						if ( typeof current_val.selected_variants === "object" && inObject( el, current_val.selected_variants ) ) {
							is_selected = ' selected="selected"';
						} else if ( typeof current_val.selected_variants === "string" && el === current_val.selected_variants) {
							is_selected = ' selected="selected"';
						}

						variants_options += '<option value="' + el + '"' + is_selected + '>' + el + '</option>';
						count_weights++;
					} );
					if ( typeof $font_weight !== "undefined" ) {
						$font_weight.html( variants_options );
						// if there is no weight or just 1 we hide the weight select ... cuz is useless
						if ( $(font_select ).data('load_all_weights') === true || count_weights <= 1 ) {
							$font_weight.parent().css('display', 'none');
						} else {
							$font_weight.parent().css('display', 'inline-block');
						}
					}
				} else if ( typeof $font_weight !== "undefined" ) {
					$font_weight.parent().css('display', 'none');
				}

				// make the subsets selector
				if ( typeof subsets !== 'undefined' && subsets !== null && subsets !== '' ) {

					value_to_add['subsets'] = subsets;
					// when a font is selected force the first subset to load
					value_to_add['selected_subsets'] = {0: subsets[0]};
					var subsets_options = '',
						count_subsets = 0;
					$.each( subsets, function( key, el ) {
						var is_selected = '';
						if ( typeof current_val.selected_subsets === "object" && inObject( el, current_val.selected_subsets ) ) {
							is_selected = ' selected="selected"';
						}

						subsets_options += '<option value="' + el + '"' + is_selected + '>' + el + '</option>';
						count_subsets++;
					} );

					if ( typeof $font_subsets !== "undefined" ) {
						$font_subsets.html( subsets_options );

						// if there is no subset or just 1 we hide the subsets select ... cuz is useless
						if ( count_subsets <= 1 ) {
							$font_subsets.parent().css('display', 'none');
						} else {
							$font_subsets.parent().css('display', 'inline-block');
						}
					}
				} else if ( typeof $font_subsets !== "undefined" ) {
					$font_subsets.parent().css('display', 'none');
				}

				$input.val( JSON.stringify( value_to_add ) );
			}
		};

		/**
		 * Function to check if a value exists in an object
		 * @param value
		 * @param obj
		 * @returns {boolean}
		 */
		var inObject = function( value, obj ) {
			for ( var k in obj ) {
				if ( !obj.hasOwnProperty( k ) ) continue;
				if ( obj[k] === value ) {
					return true;
				}
			}
			return false;
		};

		var customifyBackgroundJsControl = (function () {
			"use strict";

			var api = wp.customize;

			function init () {
				// Remove the image button
				$('.customize-control-custom_background .remove-image, .customize-control-custom_background .remove-file').unbind('click').on('click', function (e) {
					removeImage($(this).parents('.customize-control-custom_background:first'));
					preview($(this));
					return false;
				});

				// Upload media button
				$('.customize-control-custom_background .background_upload_button').unbind().on('click', function (event) {
					addImage(event, $(this).parents('.customize-control-custom_background:first'));
				});

				$('.customify_background_select').on('change', function () {
					preview($(this));
				});
			}

			// Add a file via the wp.media function
			function addImage (event, selector) {

				event.preventDefault();

				var frame;
				var jQueryel = jQuery(this);

				// If the media frame already exists, reopen it.
				if (frame) {
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

					if (attachment.attributes.type !== "image") {
						return;
					}

					selector.find('.upload').attr( 'value', attachment.attributes.url);
					selector.find('.upload-id').attr( 'value', attachment.attributes.id);
					selector.find('.upload-height').attr( 'value', attachment.attributes.height);
					selector.find('.upload-width').attr( 'value', attachment.attributes.width);

					var thumbSrc = attachment.attributes.url;
					if (typeof attachment.attributes.sizes !== 'undefined' && typeof attachment.attributes.sizes.thumbnail !== 'undefined') {
						thumbSrc = attachment.attributes.sizes.thumbnail.url;
					} else if (typeof attachment.attributes.sizes !== 'undefined') {
						var height = attachment.attributes.height;
						for (var key in attachment.attributes.sizes) {
							var object = attachment.attributes.sizes[key];
							if (object.height < height) {
								height = object.height;
								thumbSrc = object.url;
							}
						}
					} else {
						thumbSrc = attachment.attributes.icon;
					}

					selector.find('.customify_background_input.background-image').val(attachment.attributes.url);

					if (!selector.find('.upload').hasClass('noPreview')) {
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
			function preview (selector) {

				var $parent = selector.parents('.customize-control-custom_background:first');

				if ( selector.hasClass('customize-control-custom_background') ) {
					var $parent = selector;
				}

				if ( $parent.length > 0 ) {
					$parent = $($parent[0]);
				} else {
					return;
				}

				var image_holder = $parent.find('.background-preview');

				if (!image_holder) { // No preview present
					return;
				}

				var the_id = $parent.find('.button.background_upload_button').data('setting_id'),
					this_setting = api.instance(the_id);

				var background_data = {};

				$parent.find('.customify_background_select, .customify_background_input').each(function () {
					var data = $(this).serializeArray();

					data = data[0];
					if (data && data.name.indexOf('[background-') != -1) {

						background_data[ $(this).data('select_name') ] = data.value;

						//default_default[data.name] = data.value;
						//if (data.name == "background-image") {
						//	css += data.name + ':url("' + data.value + '");';
						//} else {
						//	css += data.name + ':' + data.value + ';';
						//}
					}
				});

				api.instance(the_id).set( background_data );
				//// Notify the customizer api about this change
				api.trigger('change');
				api.previewer.refresh();

				//image_holder.attr('style', css).fadeIn();
			}

			// Update the background preview
			function removeImage (parent) {
				var selector = parent.find( '.upload_button_div' );
				// This shouldn't have been run...
				if (!selector.find('.remove-image').addClass('hide')) {
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
					this_setting = api.control( customizer_id + '_control'),
					current_vals = this_setting.setting(),
					screenshot = parent.find('.preview_screenshot'),
					to_array = $.map(current_vals, function(value, index) {
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

		var getUrlVars = function(name){
			var vars = [], hash;
			var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
			for(var i = 0; i < hashes.length; i++) {
				hash = hashes[i].split('=');

				vars.push(hash[0]);
				vars[hash[0]] = hash[1];
			}

			if ( typeof vars[name] !== "undefined" ) {
				return vars[name];
			}
			return false;
		}

		var IsJsonString = function(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}
	} );
})( jQuery, window );
