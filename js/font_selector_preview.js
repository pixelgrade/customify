(function($, exports){

	$(document).ready(function(){

		var api = parent.wp.customize,
			wp_settings = api.settings.settings;

		$.each( customify_settings.settings, function( key, el){

			if ( el.type === "font" ) {
				var sliced_id = key.slice(0, -1);
				sliced_id = sliced_id.replace(customify_settings.options_name + '[', '');

				api( key, function (setting) {

					setting.bind(function (to) {

						var $values =  maybeJsonParse( to );

						if ( typeof $values.font_family !== "undefined" ) {
							maybeLoadFontFamily($values);
						}

						var CSS = get_CSS_code( this.id, $values );

						var field_style = $('#customify_font_output_for_' + sliced_id);

						field_style.html( CSS );
					});
				});
			}
		});


		var get_CSS_code = function ( ID, $values ) {

			var CSS = output = '';

			if ( typeof $values.font_family !== "undefined" ) {
				CSS += 'font-family: ' + $values.font_family + ";\n";
			}

			if ( typeof $values.font_size !== "undefined" ) {
				// @TODO how about the unit?
				CSS += 'font-size: ' + $values.font_size + "px;\n";
			}

			if ( typeof $values.letter_spacing !== "undefined" ) {
				// @TODO how about the unit?
				CSS += 'letter-spacing: ' + $values.letter_spacing + "px;\n";
			}

			if ( typeof $values.line_height !== "undefined" ) {
				// @TODO how about the unit?
				CSS += 'line-height: ' + $values.line_height + "px;\n";
			}

			if ( typeof $values.text_align !== "undefined" ) {
				CSS += 'text-align: ' + $values.text_align + ";\n";
			}

			if ( typeof $values.text_transform !== "undefined" ) {
				CSS += 'text-transform: ' + $values.text_transform + ";\n";
			}
			if ( typeof $values.text_decoration !== "undefined" ) {
				CSS += 'text-decoration: ' + $values.text_decoration + ";\n";
			}

			// get selector
			if ( CSS !== '' ) {

				output += customify_settings.settings[ID].selector + "{\n";

				output += CSS;

				output += "}\n";
			}
			return output;
		};

		var maybeLoadFontFamily = function( font ) {

			if ( font.font_family === "Metro" ) {
				// load_custom_font();
			} else if ( font.type === 'google' ) {

				var family = font.font_family;

				WebFont.load( {
					google: {families: [family]},
					classes: false,
					events: false
				} );

			} else {

			}

		}

		var load_custom_font = function (  ) {

			WebFont.load( {
				custom: {families: ['Metro']},
				urls: ['http://wptrunk.dev/wp-content/uploads/fonts/538/Metro.otf']
			} );
			var tk = document.createElement( 'script' );
			tk.src = 'http://wptrunk.dev/wp-content/uploads/fonts/538/Metro.otf';
			tk.type = 'text/css';

			var s = document.getElementById( 'customify_font_output_for_heading' );
				s.parentNode.insertBefore( tk, s );
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

	});
})(jQuery, window);
