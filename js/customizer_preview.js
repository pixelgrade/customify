;(function ($, window, document, undefined) {

	const fonts_cache = [];

	$(document).ready(function () {
		var api = parent.wp.customize,
			wp_settings = api.settings.settings;

		load_webfont_once();

		$.each(customify_settings.settings, function (key, el) {

			if (el.type === "font") {
				api(key, function (setting) {
					setting.bind(function (to) {
						let $values = maybeJsonParse(to);

                        if (typeof $values !== "undefined" ) {
                            if (typeof $values.font_family !== "undefined") {
                                maybeLoadFontFamily($values);
                            }

                            let vls = get_CSS_values(this.id, $values),
                                CSS = get_CSS_code(this.id, vls),
                                field_style = $('#customify_font_output_for_' + el.html_safe_option_id);

                            field_style.html(CSS);
                        }
					});
				});

			} else if (typeof wp_settings !== "undefined" && typeof wp_settings[key] !== "undefined" && typeof el.css !== "undefined" && typeof el.live !== 'undefined' && el.live === true) {
				api(key, function (setting) {

					setting.bind(function (to) {

						$.each(el.css, function (counter, property_config) {
							let properties = [];

							properties[property_config.property] = property_config.selector;
							if (typeof property_config.callback_filter !== "undefined") {
								properties['callback'] = property_config.callback_filter;
							}

							let css_update_args = {
								properties: properties,
								propertyValue: to,
								negative_value: property_config.hasOwnProperty( 'negative_value' ) ? property_config['negative_value'] : false
							};

							if (typeof this.unit !== 'undefined') {
								css_update_args.unit = this.unit;
							}

							// Replace all dashes with underscores thus making the CSS property safe to us in a HTML ID.
							let regex_for_multiple_replace = new RegExp('-', 'g'),
                                cssStyleSelector = '.dynamic_setting_' + el.html_safe_option_id + '_property_' + property_config.property.replace(regex_for_multiple_replace, '_') + '_' + counter;

							$(cssStyleSelector).cssUpdate(css_update_args);
						});

					});
				});
			} else if (typeof el.live === "object" && el.live.length > 0) {
				// if the live parameter is an object it means that is a list of css classes
				// these classes should be affected by the change of the text fields
				var field_class = el.live.join();

				// if this field is allowed to modify text then we'll edit this live
				if ($.inArray(el.type, ['text', 'textarea', 'ace_editor']) > -1) {
					wp.customize(key, function (value) {
						value.bind(function (text) {
							let sanitizer = document.createElement('div');

							sanitizer.innerHTML = text;
							$(field_class).html(text);
						});
					});
				}
			}
		});

		/** Bind Custom Events **/

		// api.previewer.bind('highlight',function(e){
		// 	$('.customizerHighlight').removeClass('customizerHighlight');
		//
		// 	if ( $(e).length > 0 ) {
		// 		$(e).each(function(){
		// 			$(this).addClass('customizerHighlight');
		// 		});
		// 	}
		// });

		api('live_css_edit', function (setting) {
			setting.bind(function (new_text) {
				$('#customify_css_editor_output').text(new_text);
			});
		});


		/*** HELPERS **/

		function load_webfont_once() {
			if (typeof WebFont === "undefined") {
				let tk = document.createElement('script');
				tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				tk.type = 'text/javascript';
				let s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(tk, s);
			}
		}

		const get_CSS_values = function (ID, values) {

			let store = {};

			if (typeof values.font_family !== "undefined") {
				store['font-family'] = values.font_family;
			}

			if (typeof values.selected_variants !== "undefined") {

				let variants = null;

				if (typeof values.selected_variants !== "undefined" && values.selected_variants !== null) {
					variants = values.selected_variants;
				} else if (typeof values.variants !== "undefined" && typeof values.variants[0] !== "undefined") {
					variants = values.variants[0];
				}

				// google fonts also have the italic string inside, split that
				if (variants !== null && _.isString( variants ) && variants.indexOf('italic') !== -1) {
					store['font-style'] = 'italic';
					variants = variants.replace('italic', '');
				}

				if (variants !== "") {
					if (variants === 'regular') {
						variants = 'normal';
					}

					store['font-weight'] = variants;
				}
			}

			if (typeof values.font_size !== "undefined") {
				store['font-size'] = values.font_size;
                // If the value already contains a unit (is not numeric), go with that.
                if ( isNaN(values.font_size) ) {
                    // If we have a standardized value field (as array), use that.
                    if ( typeof values.font_size.value !== "undefined" ) {
                        store['font-size'] = values.font_size.value;
                        if ( typeof values.font_size.unit !== "undefined" ) {
                            store['font-size'] += values.font_size.unit;
                        }
                    } else {
                        store['font-size'] += get_field_unit(ID, 'font-size');
                    }
                } else {
                    store['font-size'] += get_field_unit(ID, 'font-size');
                }
			}

			if (typeof values.letter_spacing !== "undefined") {
				store['letter-spacing'] = values.letter_spacing;
                // If the value already contains a unit (is not numeric), go with that.
                if ( isNaN(values.letter_spacing) ) {
                    // If we have a standardized value field (as array), use that.
                    if ( typeof values.letter_spacing.value !== "undefined" ) {
                        store['letter-spacing'] = values.letter_spacing.value;
                        if ( typeof values.letter_spacing.unit !== "undefined" ) {
                            store['letter-spacing'] += values.letter_spacing.unit;
                        }
                    } else {
                        store['letter-spacing'] += get_field_unit(ID, 'letter-spacing');
                    }
                } else {
                    store['letter-spacing'] += get_field_unit(ID, 'letter-spacing');
                }
			}

			if (typeof values.line_height !== "undefined") {
				store['line-height'] = values.line_height;
                // If the value already contains a unit (is not numeric), go with that.
                if ( isNaN(values.line_height) ) {
                    // If we have a standardized value field (as array), use that.
                    if ( typeof values.line_height.value !== "undefined" ) {
                        store['line-height'] = values.line_height.value;
                        if ( typeof values.line_height.unit !== "undefined" ) {
                            store['line-height'] += values.line_height.unit;
                        }
                    } else {
                        store['line-height'] += get_field_unit(ID, 'line-height');
                    }
                } else {
                    store['line-height'] += get_field_unit(ID, 'line-height');
                }
			}

			if (typeof values.text_align !== "undefined") {
				store['text-align'] = values.text_align;
			}

			if (typeof values.text_transform !== "undefined") {
				store['text-transform'] = values.text_transform;
			}
			if (typeof values.text_decoration !== "undefined") {
				store['text-decoration'] = values.text_decoration;
			}

			return store;
		};

		const get_CSS_code = function (ID, values) {

			let field = customify_settings.settings[ID];
			let output = '';

			if (typeof window !== "undefined" && typeof field.callback !== "undefined" && typeof window[field.callback] === "function") {
				output = window[field.callback](values, field);
			} else {
				output = field.selector + "{\n";
				$.each(values, function (k, v) {
					output += k + ': ' + v + ";\n";
				});
				output += "}\n";
			}

			return output;
		};

		const get_field_unit = function (ID, field) {
			let unit = '';
			if (typeof customify_settings.settings[ID] === "undefined" || typeof customify_settings.settings[ID].fields[field] === "undefined") {
				return unit;
			}

			if (typeof customify_settings.settings[ID].fields[field].unit !== "undefined") {
				return customify_settings.settings[ID].fields[field].unit;
			} else if (typeof customify_settings.settings[ID].fields[field][3] !== "undefined") {
				// in case of an associative array
				return customify_settings.settings[ID].fields[field][3];
			}
		};

		const maybeLoadFontFamily = function (font) {

			if (typeof WebFont === "undefined") {
				let tk = document.createElement('script');
				tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				tk.type = 'text/javascript';
				let s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(tk, s);
			}

			// If the font type is not defined, we assume is a Google font.
            if ( typeof font.type === "undefined" ) {
                font.type = 'google';
            }

			if (font.type === 'theme_font') {
				WebFont.load({
					custom: {
						families: [font.font_family],
						urls: [font.src]
					}
				});
			} else if (font.type === 'google') {
				let family = font.font_family,
					variants = null,
					subsets = null;

				if (typeof font.variants !== "undefined") {
					variants = maybeJsonParse(font.variants);

					if ( typeof variants === "string" || typeof variants === "number") {
                        variants = [ variants ];
                    }

					$.each(variants, function (k, v) {

						if (k == 0) {
							family = family + ':';
						}

						family = family + v;

						if ( Object.keys(variants).length > ( parseInt(k) + 1 ) ) {
							family = family + ',';
						} else if ( typeof font.selected_subsets !== "undefined" ) {
							// in case there is a subset selected, we need to separate it from the font weight
							family = family + ':';
						}
					});
				}

				if (typeof font.selected_subsets !== "undefined") {
					subsets = maybeJsonParse(font.selected_subsets);

					$.each(subsets, function (k, v) {

						if ( k === "0" ) {
							family = family + ':';
						}

						family = family + v;

						if ( Object.keys(subsets).length > ( parseInt(k) + 1 ) ) {
							family = family + ',';
						}
					});
				}

				if (fonts_cache.indexOf(family) === -1) {
					setTimeout(function(){
						WebFont.load({
							google: {families: [family]},
							classes: false,
							events: false,
							error: function (e) {
								console.log(e);
							},
							active: function () {
								sessionStorage.fonts = true;
							}
						});
					},10);

					fonts_cache.push(family);
				}

			} else {
				// else what?
			}
		};

		const maybeJsonParse = function (value) {
			let parsed;

			if ( typeof value !== "string" ) {
			    return value;
            }

			//try and parse it, with decodeURIComponent
			try {
				parsed = JSON.parse(decodeURIComponent(value));
			} catch (e) {

				// in case of an error, treat is as a string
				parsed = value;
			}

			return parsed;
		};
	});
})(jQuery, window, document);
