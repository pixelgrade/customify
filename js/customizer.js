(function($, exports){

	$(document).ready(function(){


		// when the customizer is ready prepare our fields events
		wp.customize.bind('ready', function(){
			var api = this;

			// simple select2 field
			$('.customify_select2' ).select2();

			prepare_typography_field();

			/**
			 * Make the customizer save on CMD/CTRL+S action
			 * This is awesome!!!
			 */
			$(window).bind('keydown', function(event) {
				if (event.ctrlKey || event.metaKey) {
					switch (String.fromCharCode(event.which).toLowerCase()) {
						case 's':
							event.preventDefault();
							api.previewer.save();
							break;
					}
				}
			});
		});

		// the typography field holds a hidden input with the serialization of the google font values
		// @TODO This is wracked .. review
		var prepare_typography_field = function() {

			var $typos = $('.customify_typography_font_family' );

			$typos.each(function(){
				var font_family_select = this,
					$input = $(font_family_select).siblings('.customify_typography_values');

				//var font_family = get_typography_font_family( $input );

				// on change
				$(font_family_select).on('change',function(){
						update_siblings_selects( font_family_select );
						$input.trigger('change');
					console.log($input);
					});
				update_siblings_selects( font_family_select );
			});
		};

		$(document).on('change', '.customify_typography_font_subsets', function(ev){

			var $input = $(this).siblings('.customify_typography_values'),
				current_val =  $input.val();

			current_val = JSON.parse( current_val );
			current_val.selected_subsets = $(this).val();

			$input.val( JSON.stringify( current_val ) );

			$input.trigger('change');
		});

		$(document).on('change', '.customify_typography_font_weight', function(ev){

			var $input = $(this).siblings('.customify_typography_values' ),
				current_val =  $input.val();

			current_val = JSON.parse( current_val );
			// @todo currently the font weight selector works for one value only
			// maybe make this a multiselect
			current_val.selected_variants = { 0: $(this).val() };

			$input.val(  JSON.stringify( current_val ) );
			$input.trigger('change');
		});

		var update_siblings_selects = function ( font_select  ) {

			var selected_font = $(font_select).val(),
				$input = $(font_select).siblings('.customify_typography_values' ),
				current_val = $input.val();

			try {
				current_val = JSON.parse( current_val );
			} catch (e) {

				console.log(e);
				//return false;
			}

			var $font_weight = $(font_select).siblings('.customify_typography_font_weight');
			var $font_subsets = $(font_select).siblings('.customify_typography_font_subsets');

			var option_data = $(font_select).find( 'option[value="' + selected_font + '"]' );

			if ( option_data.length > 0  ) {

				var font_type = option_data.data('type' ),
					value_to_add = { 'type': font_type, 'font_family': selected_font},
					variants = null,
					subsets = null;

				if ( font_type == 'std' ) {
					variants = {0: '100', 1: '200', 3: '300', 4: '400', 5: '500'};
				} else {
					variants = $( option_data[0] ).data('variants' );
					subsets = $( option_data[0] ).data('subsets');
				}

				// make the variants selector
				if ( variants !== null && typeof $font_weight !== "undefined" ) {

					value_to_add['variants'] = variants;
					// when a font is selected force the first weight to load
					value_to_add['selected_variants'] = { 0: variants[0] };

					var variants_options = '';
					$.each(variants, function(key, el){
						var is_selected = '';
						if ( typeof current_val.selected_variants === "object" && inObject( el, current_val.selected_variants ) ) {
							is_selected = ' selected="selected"';
						}

						variants_options += '<option value="' + el + '"' + is_selected + '>' +el + '</option>';
					});
					$font_weight.html(variants_options);
				}

				// make the subsets selector
				if ( subsets !== null && typeof $font_subsets !== "undefined" ) {
					value_to_add['subsets'] = subsets;
					// when a font is selected force the first subset to load
					value_to_add['selected_subsets'] = { 0: subsets[0] };
					var subsets_options = '';
					$.each(subsets, function(key, el){

						var is_selected = '';
						if ( typeof current_val.selected_subsets === "object" && inObject( el, current_val.selected_subsets ) ) {
							is_selected = ' selected="selected"';
						}

						subsets_options += '<option value="' + el + '"'+is_selected+'>' +el + '</option>';
					});
					$font_subsets.html(subsets_options);
				}

				$input.val( JSON.stringify( value_to_add ) );
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

	var inObject = function( value, obj ) {
		for (var k in obj) {
			if (!obj.hasOwnProperty(k)) continue;
			if (obj[k] === value) {
				return true;
			}
		}
		return false;
	};
})(jQuery, window);