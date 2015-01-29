(function($, exports){

	$(document).ready(function(){
		// when the customizer is ready prepare our fields events
		wp.customize.bind('ready', function(){
			var api = this;

			// simple select2 field
			$('.customify_select2' ).select2();

			prepare_typography_field();

			//if ( typeof customify_settings !== "undefined" ) {
			//
			//	$.each( customify_settings, function(key, el){
			//
			//		if ( typeof el.transport === 'undefined' || typeof el.live_css === "undefined" || el.transport !== 'postMessage' ) {
			//			return;
			//		}
			//
			//		var css_rules = el.live_css;
			//
			//		//Update site title color in real time...
			//		wp.customize( key, function( value ) {
			//			console.log(css_rules);
			//			value.bind( function( newval ) {
			//				console.log('this is for sure a change, here is the new value: ');
			//				console.log(newval);
			//			} );
			//		} );
			//	});
			//}
		});


		// the typography field holds a hidden input with the serialization of the google font values
		// @TODO This is wracked .. review
		var prepare_typography_field = function() {

			var $typos = $('.customify_typography_font_family' );

			$typos.each(function(){
				var self = this,
					$current_input = $(self).siblings('.customify_typography_values');

				var font_family = get_typography_font_family( $current_input );

				//// init select2
				//$(self).select2();
				//
				//// set the current value
				//$(self).select2("val", font_family);

				// on change
				$(self)
					.on('change',function(){

						// update the font family value
						var $input = $( self ).siblings('.customify_typography_values'),
							current_val = $input.val(),
							new_val = $( self ).val();
						console.log( new_val );
						if ( typeof current_val !== 'object' ) {
							var new_json = JSON.stringify( { 'font_family': new_val } );
							$input.val( new_json );
						} else {

							var value_to_add = JSON.parse( current_val );
							value_to_add['font_family'] = new_val;
							value_to_add = JSON.stringify( value_to_add );
							$input.val( value_to_add );
						}

						// update the font-weight select options supported by the new font

						// now for backup
						//var $backup = $(self).siblings('.wrap_customify_typography_backup');

						//if ( $backup.length > 0 ) {
						//	console.log( $backup.find('select.customify_typography_backup' ).val() );
						//}

						$input.trigger('change');

						update_font_weight_select( self );
					});

				update_font_weight_select( self );
			});

		};

		var update_font_weight_select = function ( font_select  ) {

			var selected_font = $(font_select).val();

			var $font_weight = $(font_select).siblings('.customify_typography_font_weight');
			var $font_subsets = $(font_select).siblings('.customify_typography_font_subsets');

			var option_data = $(font_select).find( 'option[value="' + selected_font + '"]' );

			if ( option_data.length > 0  ) {

				var variants = $( option_data[0] ).data('variants' ),
					subsets = $( option_data[0] ).data('subsets');

				if ( typeof variants !== "undefined" && typeof $font_weight !== "undefined" ) {

					var data_variants = [];
					$.each (variants, function( key, el) {
						data_variants.push({id: el, text: el});
					});

					$font_weight.select2({
						allowClear: false,
						data:data_variants
					});
console.log( data_variants );
					$font_weight.select2("val", data_variants[0].id);
				}

				if ( typeof subsets !== "undefined" && $font_subsets !== "undefined" ) {
					var data_subsets = [];
					$.each (subsets, function( key, el) {
						data_subsets.push({id: el, text: el});
					});

					$font_subsets.select2({
						allowClear: false,
						data:data_subsets
					});
console.log( data_subsets );
					$font_subsets.select2("val", data_subsets[0].id);
				}
			}

		};


		var get_typography_font_family = function( $el ) {

			var font_family_value = $el.val();

			// first time this will not be a json so catch that error
			try {
				font_family_value = JSON.parse( font_family_value );
			} catch (e) {
				return {font_family: font_family_value};
			}

			if ( typeof font_family_value.font_family !== 'undefined' ) {
				return font_family_value.font_family;
			}

			return false;
		};
	});

})(jQuery, window);