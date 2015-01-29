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

			if ( typeof wp_settings[key] !== "undefined" && typeof el.live_css !== "undefined" && typeof el.transport !== 'undefined' && el.transport === 'postMessage' ) {

				var this_setting = wp_settings[key];

				var sliced_id = key.slice(0, -1);
				sliced_id = sliced_id.replace(customify_settings.options_name + '[', '');

				api( key, function (setting) {

					setting.bind(function (to) {
						var properties = [],
							counter = 0;

						$.each(el.live_css, function (counter, rule_config) {
							properties[rule_config.rule] = rule_config.selector;
							counter++;

							$('#dynamic_setting_' + sliced_id + '_rule_' + rule_config.rule.replace('-', '_') ).cssUpdate({
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