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
		this._cssrules = CSSOM.parse($(this.element).html());
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			this.changeRules();
		},

		changeRules: function () {

			var self = this,
				css = this._cssrules.cssRules;

			if ( css[0].hasOwnProperty('media') ) {

				// in this case we run a media query object
				$.each(css, function( key, media_query ){

					// simple object with css rules
					// change them with new ones
					$.each(media_query.cssRules, function(i, rule){
						var rule_name = rule.style[0];
						css[key].cssRules[i].style[rule_name] = self.updateCssRule(rule_name, self.settings, css[key].cssRules[i].selectorText);
					});

				});

			} else {

				// simple object with css rules
				// change them with new ones
				$.each(css, function(i, rule){
					if ( rule.hasOwnProperty( 'style' ) ) {
						var rule_name = rule.style[0];
						css[i].style[rule_name] = self.updateCssRule(rule_name, self.settings, css[i].selectorText);
					}
				});
			}

			$(window).trigger('resize');

			//Insert the new rules into <style> tag
			$(this.element).html(this._cssrules.toString());
		},

		/**
		 * Update one css rules by the given params
		 * @param rule_name
		 * @param settings
		 * @param selectorText
		 * @returns {string}
		 */
		updateCssRule: function(rule_name, settings, selectorText ){

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
			// if there is a negative rule ... keep it negative
			is_negative = self.is_negative_rule(rule_name, properties, selectorText );

			var unit = '';
			if ( px_dependents.indexOf(rule_name) != -1 ) {
				unit = 'px';
			}

			return is_negative + new_value + unit;

		},

		/**
		 * Check is the current rule has a negative selector in our config
		 * if this is true return the sign "-" which will be put in front of the value
		 * @param rule
		 * @param current_properties
		 * @param selectorText
		 * @returns {string}
		 */
		is_negative_rule: function(rule, current_properties, selectorText){
			if ( current_properties.hasOwnProperty(rule) ) {

				if ( current_properties[rule].hasOwnProperty('negative_selector') )

					if ( current_properties[rule].negative_selector == selectorText)
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
