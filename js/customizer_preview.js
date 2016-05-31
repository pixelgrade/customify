(function($, exports){
	$(document).ready(function(){
		var api = parent.wp.customize,
			wp_settings = api.settings.settings;
		api.previewer.bind('highlight',function(e){
			$('.customizerHighlight').removeClass('customizerHighlight');

			if ( $(e).length > 0 ) {
				$(e).each(function(){
					$(this).addClass('customizerHighlight');
				});
			}
		});

		$.each( customify_settings.settings, function( key, el){

			if ( typeof wp_settings[key] !== "undefined" && typeof el.css !== "undefined" && typeof el.live !== 'undefined' && el.live === true ) {

				var sliced_id = key.slice(0, -1);
				sliced_id = sliced_id.replace(customify_settings.options_name + '[', '');

				api( key, function (setting) {

					setting.bind(function (to) {
						var properties = [];

						$.each(el.css, function (counter, property_config) {

							properties[property_config.property] = property_config.selector;
							if ( typeof property_config.callback_filter !== "undefined" ) {
								properties['callback'] = property_config.callback_filter;
							}

							var css_update_args = {
								properties: properties,
								propertyValue: to
							};

							if ( typeof this.unit !== 'undefined' ) {
								css_update_args.unit = this.unit;
							}

							var req_Exp_for_multiple_replace = new RegExp('-', 'g');
							$( '#dynamic_setting_' + sliced_id + '_property_' + property_config.property.replace(req_Exp_for_multiple_replace, '_') ).cssUpdate( css_update_args );
						});

					});
				});
			} else if ( typeof el.live === "object" && el.live.length > 0 ) {

				// if the live parameter is an object it means that is a list of css classes
				// these classes should be affected by the change of the text fields
				var field_class = el.live.join();

				// if this field is allowed to modify text then we'll edit this live
				if ( $.inArray( el.type, ['text', 'textarea', 'ace_editor']) > -1 ) {
					wp.customize( key, function( value ) {
						value.bind( function( text ) {
							$( field_class ).text( text );
						} );
					} );
				}
			}
		});


		api( 'live_css_edit', function (setting) {
			setting.bind(function (new_text) {
				$('#customify_css_editor_output' ).text(new_text);
			});
		});
	});
})(jQuery, window);
