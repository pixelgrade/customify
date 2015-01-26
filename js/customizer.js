(function($, exports){

	$(document).ready(function(){

		wp.customize.bind('ready', function(){
			var api = this;

			$('.customify_select2' ).select2();


			if ( typeof customify_settings !== "undefined" ) {

				$.each( customify_settings, function(key, el){

					if ( typeof el.transport === 'undefined' || typeof el.live_css === "undefined" || el.transport !== 'postMessage' ) {
						return;
					}

					var css_rules = el.live_css;

					//Update site title color in real time...
					wp.customize( key, function( value ) {
						console.log(css_rules);
						value.bind( function( newval ) {
							console.log('this is for sure a change, here is the new value: ');
							console.log(newval);
						} );
					} );
				});
			}
		});

	});

})(jQuery, window);