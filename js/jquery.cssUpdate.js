/*
 *  cssUpdate - v1.0.0
 */
;(function ( $, window, document, undefined ) {
	var pluginName = "cssUpdate",
		defaults = {
		properties: ["color"],
		propertyValue: "pink",
		classes: "Null"
	};

	// Plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this._cssproperties = CSSOM.parse($(this.element).html());
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			this.changeProperties();
		},

		changeProperties: function () {

			var self = this,
				css = this._cssproperties.cssRules;

			if ( typeof css[0] !== "undefined" && css[0].hasOwnProperty('media') ) {
				// in this case we run a media query object
				$.each(css, function( key, media_query ){
					// simple object with css properties
					// change them with new ones
					$.each(media_query.cssRules, function(i, property){
						var property_name = property.style[0];
						css[key].cssRules[i].style[property_name] = self.updateCssRule(property_name, self.settings, css[key].cssRules[i].selectorText);
					});
				});

			} else {

				// simple object with css properties
				// change them with new ones
				$.each(css, function(i, property){
					if ( property.hasOwnProperty( 'style' ) ) {
						var property_name = property.style[0];
						css[i].style[property_name] = self.updateCssRule(property_name, self.settings, css[i].selectorText);
					}
				});
			}

			$(window).trigger('resize');

			//Insert the new properties into <style> tag
			$(this.element).html(this._cssproperties.toString());
		},

		/**
		 * Update one css properties by the given params
		 * @param property_name
		 * @param settings
		 * @param selectorText
		 * @returns {string}
		 */
		updateCssRule: function(property_name, settings, selectorText ){

			var self = this,
				properties = settings.properties,
				new_value = settings.propertyValue,
			px_dependents = [
					'padding',
					'padding-left',
					'padding-right',
					'padding-top',
					'padding-bottom',
					'border-size',
					'margin',
					'width',
					'max-width',
					'min-width',
					'height',
					'max-height',
					'min-height',
					'margin-right',
					'margin-left',
					'margin-top',
					'margin-bottom',
					'right',
					'left',
					'top',
					'bottom',
					'font-size',
					'letter-spacing',
					'border-width',
					'border-bottom-width',
					'border-left-width',
					'border-right-width',
					'border-top-width'
				],
			// if there is a negative property ... keep it negative
			is_negative = self.is_negative_property(property_name, properties, selectorText );

			var unit = '';
			if ( px_dependents.indexOf(property_name) != -1 ) {
				unit = 'px';
			}

			if ( typeof window[settings.properties.callback] === "function" ) {
				window[settings.properties.callback](new_value, selectorText, properties, unit);
			}

			return is_negative + new_value + unit;
		},

		/**
		 * Check is the current property has a negative selector in our config
		 * if this is true return the sign "-" which will be put in front of the value
		 * @param property
		 * @param current_properties
		 * @param selectorText
		 * @returns {string}
		 */
		is_negative_property: function(property, current_properties, selectorText){
			if ( current_properties.hasOwnProperty(property) ) {

				if ( current_properties[property].hasOwnProperty('negative_selector') )

					if ( current_properties[property].negative_selector == selectorText)
						return '-';
			}

			return '';

		}
	};

	// Plugin wrapper
	$.fn[ pluginName ] = function ( options ) {

		this.each(function() {
			$.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
		});

		// chain jQuery functions
		return this;
	};

})( jQuery, window, document );
