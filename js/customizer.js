(function( $, exports ) {
	$( document ).ready( function() {
		// when the customizer is ready prepare our fields events
		wp.customize.bind( 'ready', function() {
			var api = this;

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
				var api = wp.customize;
				var this_option = $( this ).children( '[value="' + $( this ).val() + '"]' );
				var data = $( this_option ).data( 'options' );

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

				var select = field.siblings( 'select' ),
					this_option = select.find( 'option[value="' + value + '"]' );
				$( this_option[0] ).attr( 'selected', 'selected' );
				update_siblings_selects( select );
				select.trigger( 'change' );

			} else {
				setting.set( value );
			}
		};

		var update_siblings_selects = function( font_select ) {

			this.bound_once = false;
			var selected_font = $( font_select ).val(),
				$input = $( font_select ).siblings( '.customify_typography_values' ),
				current_val = $input.val();

			if ( typeof current_val === '' ) {
				return;
			}

			var $font_weight = $( font_select ).parent().siblings( 'ul.options' ).find( '.customify_typography_font_weight' );
			var $font_subsets = $( font_select ).parent().siblings( 'ul.options' ).find( '.customify_typography_font_subsets' );

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

			var option_data = $( font_select ).find( 'option[value="' + selected_font + '"]' );

			if ( option_data.length > 0 ) {

				var font_type = option_data.data( 'type' ),
					value_to_add = {'type': font_type, 'font_family': selected_font},
					variants = null,
					subsets = null;

				if ( font_type == 'std' ) {
					variants = {0: '100', 1: '200', 3: '300', 4: '400', 5: '500'};
				} else {
					variants = $( option_data[0] ).data( 'variants' );
					subsets = $( option_data[0] ).data( 'subsets' );
				}

				// make the variants selector
				if ( variants !== null && typeof $font_weight !== "undefined" ) {

					value_to_add['variants'] = variants;
					// when a font is selected force the first weight to load
					value_to_add['selected_variants'] = {0: variants[0]};

					var variants_options = '',
						count_weights = 0;

					$.each( variants, function( key, el ) {
						var is_selected = '';
						if ( typeof current_val.selected_variants === "object" && inObject( el, current_val.selected_variants ) ) {
							is_selected = ' selected="selected"';
						}

						variants_options += '<option value="' + el + '"' + is_selected + '>' + el + '</option>';
						count_weights++;
					} );
					$font_weight.html( variants_options );
					// if there is no weight or just 1 we hide the weight select ... cuz is useless
					if ( count_weights <= 1 ) {
						$font_weight.parent().hide();
					} else {
						$font_weight.parent().show();
					}
				}

				// make the subsets selector
				if ( subsets !== null && typeof $font_subsets !== "undefined" ) {
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

					$font_subsets.html( subsets_options );

					// if there is no subset or just 1 we hide the subsets select ... cuz is useless
					if ( count_subsets <= 1 ) {
						$font_subsets.parent().hide();
					} else {
						$font_subsets.parent().show();
					}
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
	} );
})( jQuery, window );