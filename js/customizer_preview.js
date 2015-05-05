(function($, exports){
	$(document).ready(function(){
		var api = parent.wp.customize,
			wp_settings = api.settings.settings;
		api.previewer.bind('highlight',function(e){
			$('.customizerHighlight').removeClass('customizerHighlight');

			if ( $(e).length > 0 ) {
				//console.log(e);
				$(e).each(function(){
					$(this).addClass('customizerHighlight');
				});
			}
		});

		$.each( customify_settings.settings, function( key, el){

			if ( typeof wp_settings[key] !== "undefined" && typeof el.css !== "undefined" && typeof el.live !== 'undefined' && el.live === true ) {

				var this_setting = wp_settings[key];
				var sliced_id = key.slice(0, -1);
				sliced_id = sliced_id.replace(customify_settings.options_name + '[', '');

				api( key, function (setting) {

					setting.bind(function (to) {
						var properties = [],
							counter = 0;

						$.each(el.css, function (counter, property_config) {

							properties[property_config.property] = property_config.selector;
							if ( typeof property_config.callback_filter !== "undefined" ) {
								properties['callback'] = property_config.callback_filter;
							}

							counter++;

							$('#dynamic_setting_' + sliced_id + '_property_' + property_config.property.replace('-', '_') ).cssUpdate({
								properties: properties,
								propertyValue: to
							});
						});

					});
				});
			}
		});
	});
})(jQuery, window);