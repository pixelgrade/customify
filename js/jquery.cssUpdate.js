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
	function cssLiveUpdater ( element, options ) {
		this.element = element;
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this._cssproperties = CSSOM.parse($(this.element).html());
		this.init();
	}



	cssLiveUpdater.prototype = {
		init: function () {
			this.changeProperties();
		},

		update_plugin: function ( options ) {
			//this.element = element;
			this.settings = $.extend( {}, defaults, options );
			//this._defaults = defaults;
			//this._name = pluginName;
			//this._cssproperties = CSSOM.parse($(this.element).html());
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
			// if there is a negative property ... keep it negative
			is_negative = self.is_negative_property(property_name, properties, selectorText );

			var unit = '';

			if ( typeof this.settings.unit !== 'undefined' ) {
				unit = this.settings.unit;
			}

			if (  unit === '' && customify_settings.px_dependent_css_props.indexOf(property_name) != -1 ) {
				unit = 'px';
			}

			if ( typeof window[settings.properties.callback] === "function" ) {
				window[settings.properties.callback](new_value, selectorText, property_name, unit );
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

			var old_plugin = $(this).data( 'plugin_cssUpdate' );

			if ( typeof old_plugin !== 'undefined' ) {
				old_plugin.update_plugin( options );
			} else {
				$.data( this, "plugin_" + pluginName, new cssLiveUpdater( this, options ) );
			}
		});

		// chain jQuery functions
		return this;
	};

})( jQuery, window, document );
