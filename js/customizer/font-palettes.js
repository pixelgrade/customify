let FontPalettes = ( function( $, exports, wp ) {

    const masterSettingIds = [
        'sm_font_primary',
        'sm_font_secondary',
        'sm_font_body'
    ];

    const defaultFontType = 'google';

    const initializePalettes = () => {
        // Cache initial settings configuration to be able to update connected fields on variation change.
        if ( typeof window.settingsClone === "undefined" ) {
            window.settingsClone = $.extend(true, {}, wp.customize.settings.settings);
        }

        // Create a stack of callbacks bound to parent settings to be able to unbind them
        // when altering the connected_fields attribute.
        if ( typeof window.connectedFieldsCallbacks === "undefined" ) {
            window.connectedFieldsCallbacks = {};
        }
    };

    const updateCurrentPalette = ( label ) => {
        const $palette = $( '.c-font-palette' );

        if ( ! $palette.length ) {
            return;
        }

        const $current = $palette.find( '.fonts.current' );
        const $next = $palette.find( '.fonts.next' );

        label = label || 'Custom Style';
        $palette.find( '.c-font-palette__name' ).text( label );

        // apply the last animate set of fonts to the "current" font palette
        _.each( masterSettingIds, function( setting_id ) {
            const font = $next.find( '.' + setting_id ).css( 'font' );
            $current.find( '.' + setting_id ).css( 'font', font );
        });

        // removing the "animate" class will put the "next" font palette out view
        // so we can update the fonts in it
        $palette.removeClass( 'animate' );

        // update the fonts in the "next" palette with the new values
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                $next.find( '.' + setting_id ).css( 'font', setting() );
            }
        });

        // trigger transition to new font palette
        setTimeout(function() {
            $palette.addClass( 'animate' );
            $palette.find( '.c-font-palette__control' ).css( 'font', wp.customize( 'sm_font_primary' )() );
        });
    };

    const alterConnectedFields = swapMap => {
	    let optionsToShow = [];
        _.each( swapMap, function( fromArray, to ) {
            if ( typeof wp.customize.settings.settings[to] !== "undefined" ) {
                let newConnectedFields = [];
                if ( fromArray instanceof Array ) {

                    _.each( fromArray, function( from ) {
                        if ( typeof window.settingsClone[from] !== "undefined" ) {
                            let oldConnectedFields;
                            if ( ! _.isUndefined( window.settingsClone[from]['connected_fields'] ) ) {
	                            oldConnectedFields = Object.values( window.settingsClone[from]['connected_fields'] );
                                newConnectedFields = newConnectedFields.concat( oldConnectedFields );
                            }
                        }
                    } );

	                newConnectedFields = Object.keys( newConnectedFields ).map( function(key) {
		                return newConnectedFields[key];
	                });
                }
                wp.customize.settings.settings[to]['connected_fields'] = newConnectedFields;


	            if ( fromArray instanceof Array && fromArray.length && newConnectedFields.length ) {
		            optionsToShow.push( to );
	            }
            }
        } );

        if ( optionsToShow.length ) {
            let optionsSelector = '.' + optionsToShow.join(', .');
            $('.c-font-palette .font').addClass('hidden').filter(optionsSelector).removeClass('hidden');
        }
    };

    const resetSettings = settings => {
        _.each( settings, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                let value = setting();
                setting.set( value + "ff" );
                setting.set( value );
            }
        });
    };

    const getConnectedFieldsCallback = function (parent_setting_data, parent_setting_id) {
        return function (new_value, old_value) {
            _.each(parent_setting_data.connected_fields, function (connected_field_data) {
                if (_.isUndefined(connected_field_data) || _.isUndefined(connected_field_data.setting_id) || !_.isString(connected_field_data.setting_id) || _.isUndefined(parent_setting_data.fonts_logic)) {
                    return;
                }

                let setting = wp.customize(connected_field_data.setting_id);
                if (_.isUndefined(setting)) {
                    return;
                }

                /* ======================
                 * Process the font logic for the master (parent) font control to get the value that should be applied to the connected (font) fields.
                 */
                let newFontData = {};
                let fonts_logic = parent_setting_data.fonts_logic;

                /* ===========
                 * We need to determine the 6 subfields values to be able to determine the value of the font field.
                 */

                // The font type is straight forward as it comes directly from the parent field font logic configuration.
                if (typeof fonts_logic.type !== "undefined") {
                    newFontData['type'] = fonts_logic.type;
                } else {
                    // We use the default
                    newFontData['type'] = defaultFontType;
                }

                // The font family is straight forward as it comes directly from the parent field font logic configuration.
                if (typeof fonts_logic.font_family !== "undefined") {
                    newFontData['font_family'] = fonts_logic.font_family;
                }

                // The selected variants (subsets) also come straight from the font logic right now.
                if (typeof fonts_logic.font_weights !== "undefined") {
                    newFontData['variants'] = fonts_logic.font_weights;
                }

                if (typeof connected_field_data.font_size !== "undefined" && false !== connected_field_data.font_size) {
                    newFontData['font_size'] = connected_field_data.font_size;

                    // The font weight (selected_variants), letter spacing and text transform all come together from the font styles (intervals).
                    // We just need to find the one that best matches the connected field given font size (if given).
                    // Please bear in mind that we expect the font logic styles to be preprocessed, without any overlapping and using numerical keys.
                    if (typeof fonts_logic.font_styles !== "undefined" && _.isArray( fonts_logic.font_styles ) && fonts_logic.font_styles.length > 0) {
                        let idx = 0;
                        while ( idx < fonts_logic.font_styles.length-1 &&
                                typeof fonts_logic.font_styles[idx].end !== "undefined" &&
                                fonts_logic.font_styles[idx].end <= connected_field_data.font_size.value ) {
                            idx++;
                        }

                        // We will apply what we've got.
                        if (typeof fonts_logic.font_styles[idx].font_weight !== "undefined") {
                            newFontData['selected_variants'] = fonts_logic.font_styles[idx].font_weight;
                        }
                        if (typeof fonts_logic.font_styles[idx].letter_spacing !== "undefined") {
                            newFontData['letter_spacing'] = fonts_logic.font_styles[idx].letter_spacing;
                        }
                        if (typeof fonts_logic.font_styles[idx].text_transform !== "undefined") {
                            newFontData['text_transform'] = fonts_logic.font_styles[idx].text_transform;
                        }
                    }

                    // The line height is determined by getting the value of the polynomial function determined by points.
                    if ( typeof fonts_logic.font_size_to_line_height_points !== "undefined" && _.isArray(fonts_logic.font_size_to_line_height_points)) {
                        let f = linear(fonts_logic.font_size_to_line_height_points);
                        newFontData['line_height'] = { value: Number(f(connected_field_data.font_size.value)).toPrecision(2) };
                    }
                }

                let serializedNewFontData = CustomifyFontSelectFields.encodeValues(newFontData);
                setting.set(serializedNewFontData);
            });
        }
    };

    const help = {};

	/**
	 * Makes argument to be an array if it's not
	 *
	 * @param input
	 * @returns {Array}
	 */

	help.makeItArrayIfItsNot = function (input) {
		return Object.prototype.toString.call( input ) !== '[object Array]'
			? [input]
			: input
	};

	/**
	 *
	 * Utilizes bisection method to search an interval to which
	 * point belongs to, then returns an index of left border
	 * of the interval
	 *
	 * @param {Number} point
	 * @param {Array} intervals
	 * @returns {Number}
	 */

	help.findIntervalLeftBorderIndex = function (point, intervals) {
		//If point is beyond given intervals
		if (point < intervals[0])
			return 0
		if (point > intervals[intervals.length - 1])
			return intervals.length - 1
		//If point is inside interval
		//Start searching on a full range of intervals
		var indexOfNumberToCompare
			, leftBorderIndex = 0
			, rightBorderIndex = intervals.length - 1
		//Reduce searching range till it find an interval point belongs to using binary search
		while (rightBorderIndex - leftBorderIndex !== 1) {
			indexOfNumberToCompare = leftBorderIndex + Math.floor((rightBorderIndex - leftBorderIndex)/2)
			point >= intervals[indexOfNumberToCompare]
				? leftBorderIndex = indexOfNumberToCompare
				: rightBorderIndex = indexOfNumberToCompare
		}
		return leftBorderIndex
	};

	function evaluateLinear(pointsToEvaluate, functionValuesX, functionValuesY) {
		var results = [];
		pointsToEvaluate = help.makeItArrayIfItsNot(pointsToEvaluate);
		pointsToEvaluate.forEach(function (point) {
			var index = help.findIntervalLeftBorderIndex(point, functionValuesX);
			if ( index === functionValuesX.length - 1) {
				index--;
            }
			results.push(linearInterpolation(point, functionValuesX[index], functionValuesY[index], functionValuesX[index + 1], functionValuesY[index + 1]));
		});
		return results
	}

	/**
	 *
	 * Evaluates y-value at given x point for line that passes
	 * through the points (x0,y0) and (y1,y1)
	 *
	 * @param x
	 * @param x0
	 * @param y0
	 * @param x1
	 * @param y1
	 * @returns {Number}
	 */
	function linearInterpolation(x, x0, y0, x1, y1) {
		var a = (y1 - y0) / (x1 - x0);
		var b = -a * x0 + y0;
		return a * x + b
	}

	const linear = function(points) {
	    let xvalues = [];
	    let yvalues = [];

	    for (let i = 0; i < points.length; i++) {
	        xvalues.push(i[0]);
	        yvalues.push(i[1]);
        }

	    return function( x ) {
		    if ( points.length === 0 ) {
			    return 0;
		    }
		    return evaluateLinear([x], xvalues, yvalues)[0];
        }
    };

    // Neville's algorithm for polynomial interpolation.
    const interpolatingPolynomial = function (points) {
        let n = points.length - 1, p;

        p = function (i, j, x) {
            if (i === j) {
                return points[i][1];
            }

            return ((points[j][0] - x) * p(i, j - 1, x) +
                (x - points[i][0]) * p(i + 1, j, x)) /
                (points[j][0] - points[i][0]);
        };

        return function (x) {
            if (points.length === 0) {
                return 0;
            }
            return p(0, n, x);
        };
    };

    const bindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize( parent_setting_id );

                if ( typeof parent_setting_data.connected_fields !== "undefined" ) {
                    connectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback( parent_setting_data, parent_setting_id );
                    parent_setting.bind( connectedFieldsCallbacks[parent_setting_id] );
                }
            }
        } );
    };

    const unbindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize(parent_setting_id);

                if (typeof parent_setting_data.connected_fields !== "undefined" && typeof connectedFieldsCallbacks[parent_setting_id] !== "undefined") {
                    parent_setting.unbind(connectedFieldsCallbacks[parent_setting_id]);
                }
                delete connectedFieldsCallbacks[parent_setting_id];
            }
        } );
    };

    // Alter connected fields of the master fonts controls depending on the selected palette variation.
    const reloadConnectedFields = () => {
//        const setting = wp.customize( 'sm_font_palette_variation' );
//
//        if ( _.isUndefined( setting ) ) {
//            return;
//        }
//
//        const variation = setting();
//
//        if ( ! window.fontPalettesVariations.hasOwnProperty( variation ) ) {
//            return;
//        }

        unbindConnectedFields();
//        alterConnectedFields( fontPalettesVariations[variation] );
        bindConnectedFields();
    };

    const createCurrentPaletteControls = () => {
        let $palette = $( '.c-font-palette' );

        if ( ! $palette.length ) {
            return;
        }

        let $fonts = $palette.find( '.fonts.next .font' );
    };

    const onPaletteChange = function() {
        let $label = $( this ).next( 'label' ).clone();
        let label;

        $label.find( '.preview__letter' ).remove();
        label = $label.text();
        $label.remove();

        // Take the fonts config for each setting and distribute it to each (master) setting.
        let data = $( this ).data( 'fonts_logic' );
        if ( ! _.isUndefined( data ) ) {
            $.each( data, function( setting_id, config ) {
                set_field_fonts_logic_config( setting_id, config );
            } );
        }

        // In case this palette has values (options) attached to it, let it happen.
        $( this ).trigger( 'customify:preset-change' );
        updateCurrentPalette( label );
    };

    const set_field_fonts_logic_config = function( setting_id, config ) {
        wp.customize.settings.settings[setting_id].fonts_logic = config;

        // We also need to trigger a fake setting value change since the master font controls don't usually hold a (usable) value.
        let setting = wp.customize( setting_id );
        if ( _.isUndefined( setting ) ) {
            return;
        }

        // We will set the entire config as the master font field value just because it ensures us that,
        // when new info arrives, the setting callbacks will be fired (.set() doesn't do anything if the new value is the same as the old).
        // Also some entries will be used to set the master font subfields (mainly font family).
        // This value is not used in any other way!
        let serializedNewFontData = CustomifyFontSelectFields.encodeValues(config);
        setting.set(serializedNewFontData);
    };

    const handlePalettes = () => {
        initializePalettes();
        createCurrentPaletteControls();
	    reloadConnectedFields();
        updateCurrentPalette();

        $( document ).on( 'click', '.customify_preset.font_palette input', onPaletteChange );
    };

    wp.customize.bind( 'ready', handlePalettes );

    return {
        masterSettingIds: masterSettingIds
    };

} )( jQuery, window, wp );