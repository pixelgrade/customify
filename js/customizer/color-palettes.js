let ColorPalettes = ( function( $, exports, wp ) {

	const defaultVariation = 'light';
    const masterSettingIds = [
        "sm_color_primary",
        "sm_color_secondary",
        "sm_color_tertiary",
        "sm_dark_primary",
        "sm_dark_secondary",
        "sm_dark_tertiary",
        "sm_light_primary",
        "sm_light_secondary",
        "sm_light_tertiary"
    ];

	const filteredColors = {
        sm_color_primary: '',
        sm_color_secondary: '',
        sm_color_tertiary: '',
        sm_dark_primary: '',
        sm_dark_secondary: '',
        sm_dark_tertiary: '',
        sm_light_primary: '',
        sm_light_secondary: '',
        sm_light_tertiary: ''
    };

	const primary_color_selector = '#_customize-input-sm_dark_color_primary_slider_control';
	const secondary_color_selector = '#_customize-input-sm_dark_color_secondary_slider_control';
	const tertiary_color_selector = '#_customize-input-sm_dark_color_tertiary_slider_control';
	const color_sliders_selector = primary_color_selector + ', ' + secondary_color_selector + ', ' + tertiary_color_selector;

    let setupGlobalsDone = false;

    const setupGlobals = () => {

        if ( setupGlobalsDone ) {
            return;
        }

        // Cache initial settings configuration to be able to update connected fields on variation change.
        if ( typeof window.settingsClone === "undefined" ) {
            window.settingsClone = $.extend(true, {}, wp.customize.settings.settings);
        }

        // Create a stack of callbacks bound to parent settings to be able to unbind them
        // when altering the connected_fields attribute.
        if ( typeof window.colorsConnectedFieldsCallbacks === "undefined" ) {
            window.colorsConnectedFieldsCallbacks = {};
        }

        setupGlobalsDone = true;
    };

	const hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];

	function hex( x ) {
		return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
	}

    function rgb2hex( color ) {
        return '#' + hex( color[0] ) + hex( color[1] ) + hex( color[2] );
    }

    function hsl2hex( color ) {
        var rgb = hsl2Rgb( color.hue, color.saturation, color.lightness );
        return rgb2hex( rgb );
    }

	function hex2rgba( hex ) {
		var matches = /^#([A-Fa-f0-9]{3,4}){1,2}$/.test( hex );
        var r = 0, g = 0, b = 0, a = 0;
		if ( matches ) {
			hex = hex.substring(1).split('');
			if ( hex.length === 3 ) {
				hex = [hex[0], hex[0], hex[1], hex[1], hex[2], hex[2], 'F', 'F'];
			}
            if ( hex.length === 4 ) {
                hex = [hex[0], hex[0], hex[1], hex[1], hex[2], hex[2], hex[3], hex[3]];
            }
            r = parseInt( [ hex[0], hex[1] ].join(''), 16 );
            g = parseInt( [ hex[2], hex[3] ].join(''), 16 );
            b = parseInt( [ hex[4], hex[5] ].join(''), 16 );
            a = parseInt( [ hex[6], hex[7] ].join(''), 16 );
		}
        var hsl = rgbToHsl(r, g, b);
        var rgba = {
            red: r,
            green: g,
            blue: b,
            alpha: a,
            hue: hsl[0],
            saturation: hsl[1],
            lightness: hsl[2],
            luma: 0.2126 * r + 0.7152 * g + 0.0722 * b
        };
        return rgba;
	}

    function rgbToHsl(r, g, b){
        r /= 255, g /= 255, b /= 255;
        var max = Math.max(r, g, b), min = Math.min(r, g, b);
        var h, s, l = (max + min) / 2;

        if(max == min){
            h = s = 0; // achromatic
        }else{
            var d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch(max){
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return [h, s, l];
    }

    const resetSettings = () => {
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                let value = setting();
	            let parent_setting_data = wp.customize.settings.settings[setting_id];

	            _.each( parent_setting_data.connected_fields, function( connected_field_data ) {
		            if ( _.isUndefined( connected_field_data ) || _.isUndefined( connected_field_data.setting_id ) || ! _.isString( connected_field_data.setting_id ) ) {
			            return;
		            }
		            const connected_setting = wp.customize( connected_field_data.setting_id );
		            if ( _.isUndefined( connected_setting ) ) {
			            return;
		            }
		            connected_setting.set( getFilteredColor( setting_id ) );
	            } );
            }
        });
    };

    const updateFilteredColors = () => {
	    _.each( masterSettingIds, function( setting_id ) {
		    const setting = wp.customize( setting_id );

		    if ( typeof setting !== "undefined" ) {
			    let value = setting();
			    filteredColors[setting_id] = filterColor( value );
		    }
	    });
    };

    const getFilteredColor = ( setting_id ) => {
        return filteredColors[setting_id];
    };

    const getMasterFieldCallback = function( parent_setting_data, parent_setting_id ) {
        return function( new_value, old_value ) {
            _.each( parent_setting_data.connected_fields, function( connected_field_data ) {
                if ( _.isUndefined( connected_field_data ) || _.isUndefined( connected_field_data.setting_id ) || ! _.isString( connected_field_data.setting_id ) ) {
                    return;
                }
                const setting = wp.customize( connected_field_data.setting_id );
                if ( _.isUndefined( setting ) ) {
                    return;
                }
                setting.set( getFilteredColor( parent_setting_id ) );
            } );
	        updateFilterPreviews();
        }
    };

    const bindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize(parent_setting_id);

                if ( ! _.isUndefined( parent_setting_data.connected_fields ) ) {
                    window.colorsConnectedFieldsCallbacks[parent_setting_id] = getMasterFieldCallback(parent_setting_data, parent_setting_id);
                    parent_setting.bind(window.colorsConnectedFieldsCallbacks[parent_setting_id]);

                    _.each( parent_setting_data.connected_fields, function( connected_field_data ) {
                        let connected_setting_id = connected_field_data.setting_id;
                        let connected_setting = wp.customize(connected_setting_id);

                        if ( typeof connected_setting !== "undefined" ) {
                            window.colorsConnectedFieldsCallbacks[connected_setting_id] = toggleAlteredClassOnMasterControls;
                            connected_setting.bind(window.colorsConnectedFieldsCallbacks[connected_setting_id]);
                        }
                    } );
                }
            }
        } );
    };

    const unbindConnectedFields = function() {
        _.each( window.colorsConnectedFieldsCallbacks, function( callback, setting_id ) {
            let setting = wp.customize(setting_id);
            setting.unbind( callback );
        } );
        window.colorsConnectedFieldsCallbacks = {};
    };

    // alter connected fields of the master colors controls depending on the selected palette variation
    const getCurrentVariation = () => {
	    const setting = wp.customize( 'sm_color_palette_variation' );

	    if ( _.isUndefined( setting ) ) {
		    return defaultVariation;
	    }

	    const variation = setting();

	    if ( ! window.colorPalettesVariations.hasOwnProperty( variation ) ) {
	    	return defaultVariation;
	    }

	    return variation;
    };

    const getSwapMap = ( variation ) => {
        if ( ! window.colorPalettesVariations.hasOwnProperty( variation ) ) {
            return defaultVariation;
        }
        return window.colorPalettesVariations[variation];
    };

    // return an array with the hex values of the current palette
    const getCurrentPaletteColors = () => {
        const colors = [];
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );
            const color = setting();
            colors.push( color );
        } );
        return colors;
    };

	function hsl2Rgb(h, s, l){
		var r, g, b;

		if(s == 0){
			r = g = b = l; // achromatic
		}else{
			var hue2rgb = function hue2rgb(p, q, t){
				if(t < 0) t += 1;
				if(t > 1) t -= 1;
				if(t < 1/6) return p + (q - p) * 6 * t;
				if(t < 1/2) return q;
				if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
				return p;
			};

			var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
			var p = 2 * l - q;
			r = hue2rgb(p, q, h + 1/3);
			g = hue2rgb(p, q, h);
			b = hue2rgb(p, q, h - 1/3);
		}

		return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
	}

    function mixRGB(color1, color2, ratio) {
        ratio = ratio || 0.5;
        color1.red = parseInt( color2.red * ratio + color1.red * ( 1 - ratio ), 10);
        color1.green = parseInt( color2.green * ratio + color1.green * ( 1 - ratio ), 10);
        color1.blue = parseInt( color2.blue * ratio + color1.blue * ( 1 - ratio ), 10);
        return hex2rgba( rgb2hex( [color1.red, color1.green, color1.blue] ) );
    }

    function mix(property, color1, color2, ratio) {
        return color1[property] * ( 1 - ratio ) + color2[property] * ratio;
    }

    function mixValues( value1, value2, ratio ) {
        return value1 * ( 1 - ratio ) + value2 * ratio;
    }

    const filterColor = ( color, filter ) => {
        filter = typeof filter === "undefined" ? $( '[name*="sm_palette_filter"]:checked' ).val() : filter;

        let newColor = hex2rgba( color );
        var palette = getCurrentPaletteColors();
        var paletteColors = palette.slice(0,3);
        var paletteDark = palette.slice(3,6);
        var average = getAveragePixel( getPixelsFromColors( palette ) );
        var averageColor = getAveragePixel( getPixelsFromColors( paletteColors ) );
        var averageDark = getAveragePixel( getPixelsFromColors( paletteDark ) );

        // Intensity Filters
        if ( filter === 'vivid' ) {
            newColor = hsl2Rgb( newColor.hue, mixValues( newColor.saturation, 1, 0.5 ), newColor.lightness );
            return rgb2hex( newColor );
        }

        if ( filter === 'warm' && color !== palette[0] ) {
            var sepia = hex2rgba( '#704214' );
            sepia.saturation = mix( 'saturation', sepia, newColor, 1 );
            sepia.lightness = mix( 'lightness', sepia, newColor, 1 );
            sepia = hex2rgba( hsl2hex( sepia ) );
            newColor.saturation = newColor.saturation * 0.75;
            newColor = hex2rgba( hsl2hex( newColor ) );
            newColor = mixRGB( newColor, sepia, 0.75 );

            newColor.lightness = mix( 'lightness', newColor, hex2rgba( newColor.lightness > 0.5 ? '#FFF' : '#000' ), 0.2 );
	        return hsl2hex( newColor );
        }

        if ( filter === 'softer' ) {
            // if ( paletteColors.indexOf( color ) !== -1 ) {
            //     newColor = mixRGB( newColor, averageColor, 0.5 );
            //     return rgb2hex( [ newColor.red, newColor.green, newColor.blue ] );
            // }
            newColor.saturation = mix( 'saturation', newColor, hex2rgba( '#FFF' ), 0.3 );
            newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#FFF' ), 0.1 );
            // newColor.hue = mix( 'hue', newColor, averageColor, 1 );
            return hsl2hex( newColor );
        }

        if ( filter === 'pastel' ) {
            newColor.saturation = mix( 'saturation', newColor, hex2rgba( '#FFF' ), 0.6 );
            newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#FFF' ), 0.2 );
            return hsl2hex( newColor );
        }

        if ( filter === 'greyish' ) {
            newColor = hsl2Rgb( newColor.hue, mixValues( newColor.saturation, 0, 0.8 ), newColor.lightness );
            return rgb2hex(newColor);
        }

        // Custom (Real) Filters
        if ( filter === 'clarendon' ) {
            // Color Group
            // Slightly increase saturation
            if ( color === palette[0] || color === palette[1] || color === palette[2] ) {
                newColor = hsl2Rgb( newColor.hue, mixValues( newColor.saturation, 1, 0.3 ), newColor.lightness );
                return rgb2hex( newColor );
            }

            // Dark Group
            // Add dark to darker colors
            if ( color === palette[3] || color === palette[4] || color === palette[5] ) {
                newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#000' ), 0.4 );
            }

            // Light Group
            // Add light to lighter colors
            if ( color === palette[6] || color === palette[7] || color === palette[8] ) {
                newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#FFF' ), 0.4 );
            }

            return hsl2hex( newColor );
        }

        // Inactive Below
        if ( filter === 'cold' && color !== palette[0] ) {
            var targetHue = 0.55;

            newColor.saturation = mix( 'saturation', newColor, hex2rgba( '#FFF' ), 0.4 );
            newColor.hue = ( newColor.hue - targetHue ) / 18 + targetHue;
            newColor = hex2rgba( hsl2hex( newColor ) );

            // increase contrast ( saturation +10%, lightness +/- 20% );
            var newColorHSL = rgbToHsl( newColor.red, newColor.green, newColor.blue );
            newColor.hue = newColorHSL[0];
            newColor.saturation = mixValues( newColorHSL[1], 1, 0.1 );
            newColor.lightness = mix( 'lightness', newColor, hex2rgba( newColor.lightness > 0.5 ? '#FFF' : '#000' ), 0.2 );
            return hsl2hex( newColor );
        }

        if ( filter === 'dumb' ) {

            if ( color === palette[1] || color === palette[2] ) {
                newColor = hex2rgba(palette[0]);
                newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#000' ), 0.2 );
                newColor.saturation = mix( 'saturation', newColor, hex2rgba( '#000' ), 0.2 );

                if ( color === palette[2] ) {
                    newColor.lightness = mix( 'lightness', newColor, hex2rgba( '#000' ), 0.2 );
                    newColor.saturation = mix( 'saturation', newColor, hex2rgba( '#000' ), 0.2 );
                }
                return hsl2hex( newColor );
            } else {
                newColor.hue = hex2rgba(palette[0]).hue;
                return hsl2hex( newColor );
            }
        }

        if ( filter === 'mayfair' ) {
            if ( color === palette[1] || color === palette[2] ) {
                newColor = hex2rgba(palette[0]);
                newColor.hue = ( newColor.hue + 0.05 ) % 1;
                if ( color === palette[2] ) {
                    newColor.hue = ( newColor.hue + 0.05 ) % 1;
                }
                return hsl2hex( newColor );
            } else {
                newColor.hue = hex2rgba(palette[0]).hue;
                return hsl2hex( newColor );
            }
        }


        if ( filter === 'sierra' ) {
            if ( color === palette[1] || color === palette[2] ) {
                newColor = hex2rgba(palette[0]);
                newColor.hue = ( newColor.hue + 0.95 ) % 1;
                if ( color === palette[2] ) {
                    newColor.hue = ( newColor.hue + 0.95 ) % 1;
                }
                return hsl2hex( newColor );
            } else {
                newColor.hue = hex2rgba(palette[0]).hue;
                return hsl2hex( newColor );
            }
        }

        return color;
    };

    const createCurrentPaletteControls = () => {
        const $palette = $( '.c-color-palette' );
        const $fields = $palette.find( '.c-color-palette__fields' ).find( 'input' );

        if ( ! $palette.length ) {
            return;
        }

        const $colors = $palette.find( '.colors .color' );

        $colors.each( ( i, obj ) => {
            const $obj = $( obj );
            const setting_id = $obj.data( 'setting' );
            const $input = $fields.filter( '.' + setting_id );
            const setting = wp.customize( setting_id );

            $obj.data( 'target', $input );

            $input.iris( {
                change: ( event, ui ) => {
                    const currentColor = ui.color.toString();

                    $obj.css( 'color', filterColor( currentColor ) );

	                filteredColors[setting_id] = filterColor( currentColor );
                    setting.set( currentColor );

                    if ( event.originalEvent.type !== 'external' ) {
                        $palette.find( '.color.' + setting_id ).removeClass( 'altered' );
                    }

	                setPalettesOnConnectedFields();
//                    buildColorMatrix();
                },
            } );

	        $obj.find( '.iris-picker' ).on( 'click', function( e ) {
		        e.stopPropagation();
		        e.preventDefault();
            } );

            const showColorPicker = () => {
                $colors.not( $obj ).each( function( i, obj ) {
                    $( obj ).data( 'target' ).not( $input ).hide();
                } );
                $input.show().focus();
            };

	        $obj.on( 'click', ( e ) => {
                e.stopPropagation();
                e.preventDefault();

                if ( $input.is( ':visible' ) ) {
                    $input.iris( 'hide' );
                    $input.hide();
                    $colors.removeClass( 'active inactive' );
                } else {
                    if ( $obj.is( '.altered' ) ) {
                        confirmChanges( showColorPicker );
                    } else {
                        showColorPicker();
                    }
                }
            } );

	        $input.on( 'click', ( e ) => {
		        e.stopPropagation();
		        e.preventDefault();
	        } );

	        $input.on( 'focus', ( e ) => {
		        $colors.not( $obj ).addClass( 'inactive' ).removeClass( 'active' );
		        $obj.addClass( 'active' ).removeClass( 'inactive' );

                $colors.not( $obj ).each( function( i, other ) {
                    $( other ).data( 'target' ).iris( 'hide' );
                } );

                const $iris = $input.next( '.iris-picker' );
                const paletteWidth = $palette.find( '.c-color-palette__colors' ).outerWidth();
                const $visibleColors = $colors.filter( ':visible' );
                const index = $visibleColors.index( $obj );

                $iris.css( 'left', ( paletteWidth - 200 ) * index / ( $visibleColors.length - 1 ) );

                showOldColors();

                $input.iris( 'show' );
            } );

            $input.on( 'focusout', ( e ) => {
                showNewColors();
            });
        } );

        $( 'body' ).on( 'click', function() {
            $colors.removeClass( 'active inactive' );
	        $colors.each( function( i, obj ) {
	            const $input = $( obj ).data( 'target' );

		        $input.iris( 'hide' );
		        $input.hide();
	        } );
        } );
    };

    const showNewColors = function() {
        _.each(masterSettingIds, function( id ) {
            $( '.c-color-palette' ).find( '.color.' + id ).css( 'color', getFilteredColor( id ) );
        });
    };

    const showOldColors = function() {
        _.each(masterSettingIds, function( id ) {
            const setting = wp.customize( id );
            const initialColor = setting();
            $( '.c-color-palette' ).find( '.color.' + id ).css( 'color', initialColor );
        });
    };

    const onPaletteChange = function() {
        $( this ).trigger( 'customify:preset-change' );
	    updateFilteredColors();
	    reinitializeConnectedFields();
    };

    // this function goes through all the connected fields and adds swatches to the default color picker for all the colors in the current color palette
    const setPalettesOnConnectedFields = _.debounce( () => {
        let $targets = $();
        // loop through the master settings
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                if ( ! _.isUndefined( parent_setting_data.connected_fields ) )  {
                    // loop through all the connected fields and search the element on which the iris plugin has been initialized
                    _.each( parent_setting_data.connected_fields, function( connected_field_data ) {
                        // the connected_setting_id is different than the actual id attribute of the element we're searching for
                        // so we have to do some regular expressions
                        let connected_setting_id = connected_field_data.setting_id;
                        let matches = connected_setting_id.match(/\[(.*?)\]/);

                        if ( matches ) {
                            let target_id = matches[1];
                            let $target = $( '.customize-control-color' ).filter( '[id*="' + target_id + '"]' ).find( '.wp-color-picker' );
                            $targets = $targets.add( $target );
                        }
                    });
                }
            }
        });
        // apply the current color palettes to all the elements found
        $targets.iris({ palettes: getCurrentPaletteColors() });
    }, 30 );

    const buildColorMatrix = () => {
        const $matrix = $( '.sm_color_matrix' );

        if ( ! $matrix.children().length ) {
            _.each( masterSettingIds, function( setting_id ) {
                $( '<div class="' + setting_id + '">' ).appendTo( $matrix );
            } );
        }

	    _.each( masterSettingIds, function( setting_id ) {
            const $bucket = $matrix.children( '.' + setting_id );
            let classes = [];

            $bucket.css( 'color', getFilteredColor( setting_id ) );

		    _.each( wp.customize.settings.settings[setting_id]['connected_fields'], function( connected_field ) {
                const field_id = connected_field.setting_id;
                const fieldClassName = field_id.replace( '[', '_' ).replace( ']', '' );
                classes.push( fieldClassName );

                if ( ! $bucket.children( '.' + fieldClassName ).length ) {
	                $( '<div title="' + field_id + '" class="' + fieldClassName + '">' ).appendTo( $bucket );
                }
            } );

            let className =  '.' + classes.join( ', .' );

            if ( classes.length ) {
                $bucket.children().not( className ).remove();
            } else {
                $bucket.children().remove();
            }
	    });
    };

    const toggleAlteredClassOnMasterControls = _.debounce( () => {
        let alteredSettings = [];
        let alteredSettingsSelector;

        _.each( masterSettingIds, function( masterSettingId ) {
            let connectedFields = wp.customize.settings.settings[masterSettingId]['connected_fields'];
            let masterSettingValue = wp.customize( masterSettingId )();
            let connectedFieldsWereAltered = false;

            if ( ! _.isUndefined( connectedFields ) && ! Array.isArray( connectedFields ) ) {
                connectedFields = Object.keys( connectedFields ).map( function(key) {
                    return connectedFields[key];
                });
            }

            if ( ! _.isUndefined( connectedFields ) && connectedFields.length ) {
                _.each( connectedFields, function( connectedField ) {
                    let connectedSettingId = connectedField.setting_id;
                    let connectedSetting = wp.customize( connectedSettingId );

                    if ( typeof connectedSetting !== "undefined" ) {
                        let connectedFieldValue = connectedSetting();

                        if ( typeof connectedFieldValue === "string" && connectedFieldValue.toLowerCase() !== filterColor( masterSettingValue ).toLowerCase() ) {
                            connectedFieldsWereAltered = true;
                        }
                    }
                } );

                if ( connectedFieldsWereAltered ) {
                    alteredSettings.push( masterSettingId );
                }
            }
        } );

        alteredSettingsSelector = '.' + alteredSettings.join(', .');

        $( '.c-color-palette .color' ).removeClass( 'altered' );

        if ( alteredSettings.length ) {
             $( '.c-color-palette .color' ).filter( alteredSettingsSelector ).addClass( 'altered' );
        }

    }, 30 );

    const toggleHiddenClassOnMasterControls = _.debounce( () => {
        let optionsToShow = [];
        let optionsSelector;

        _.each( masterSettingIds, function( masterSettingId ) {
            let connectedFields = wp.customize.settings.settings[masterSettingId]['connected_fields'];

            if ( ! _.isUndefined( connectedFields ) && ! _.isEmpty( connectedFields ) ) {
                optionsToShow.push( masterSettingId );
            }
        } );

        optionsSelector = '.' + optionsToShow.join(', .');

        $( '.sm-palette-filter .color' ).addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' );
	    $( '.c-color-palette .color' ).addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' );
        $( '.customify_preset.color_palette .palette__item' ).addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' );
    }, 30 );

    const refreshCurrentPaletteControl = () => {
        toggleAlteredClassOnMasterControls();
        toggleHiddenClassOnMasterControls();
        setPalettesOnConnectedFields();
        showNewColors();
    };

	const swapConnectedFields = ( settings, swapMap ) => {
        let newSettings = JSON.parse(JSON.stringify(settings));
        let oldSettings = JSON.parse(JSON.stringify(settings));

		_.each( swapMap, function( fromArray, to ) {
			if ( typeof newSettings[to] !== "undefined" ) {
				let newConnectedFields = [];
				if ( fromArray instanceof Array ) {
					_.each( fromArray, function( from ) {
						let oldConnectedFields;
						if ( _.isUndefined( oldSettings[from]['connected_fields'] ) ) {
							oldSettings[from]['connected_fields'] = [];
						}
						oldConnectedFields = Object.values( oldSettings[from]['connected_fields'] );
						newConnectedFields = newConnectedFields.concat( oldConnectedFields );
					} );
				}
				newSettings[to]['connected_fields'] = Object.keys( newConnectedFields ).map( function(key) {
					return newConnectedFields[key];
				});
			}
		} );
		return _.clone(newSettings);
	};

    const moveConnectedFields = ( oldSettings, from, to, ratio ) => {

        let settings = _.clone( oldSettings );

	    if ( ! _.isUndefined( settings[to] ) && ! _.isUndefined( settings[from] ) ) {

            if ( _.isUndefined( settings[from]['connected_fields'] ) ) {
                settings[from]['connected_fields'] = [];
            }

            if ( _.isUndefined( settings[to]['connected_fields'] ) ) {
                settings[to]['connected_fields'] = [];
            }

		    const oldFromConnectedFields = Object.values( settings[from]['connected_fields'] );
		    const oldToConnectedFields = Object.values( settings[to]['connected_fields'] );
		    const oldConnectedFields = oldToConnectedFields.concat( oldFromConnectedFields );
		    const count = Math.round( ratio * oldConnectedFields.length );

		    let newToConnectedFields = oldConnectedFields.slice( 0, count );
		    let newFromConnectedFields = oldConnectedFields.slice( count );

		    newToConnectedFields = Object.keys( newToConnectedFields ).map( function(key) {
			    return newToConnectedFields[key];
		    });
		    newToConnectedFields = Object.keys( newToConnectedFields ).map( function(key) {
			    return newToConnectedFields[key];
		    });

		    settings[to]['connected_fields'] = newToConnectedFields;
		    settings[from]['connected_fields'] = newFromConnectedFields;
	    }

	    return settings;
	};

	const reloadConnectedFields = () => {
		let tempSettings = JSON.parse(JSON.stringify(window.settingsClone));

        if ( typeof $( '[name*="sm_color_diversity"]:checked' ).data( 'default' ) === 'undefined'
            || typeof $( '[name*="sm_coloration_level"]:checked' ).data( 'default' ) === 'undefined' ) {
	        const primaryRatio = $( primary_color_selector ).val() / 100;
	        const secondaryRatio = $( secondary_color_selector ).val() / 100;
	        const tertiaryRatio = $( tertiary_color_selector ).val() / 100;

	        tempSettings = moveConnectedFields( tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio );
	        tempSettings = moveConnectedFields( tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio );
	        tempSettings = moveConnectedFields( tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio );

	        var diversity = $( '[name*="sm_color_diversity"]:checked' ).val();
            var diversity_variation = getSwapMap( 'color_diversity_low' );
            tempSettings = swapConnectedFields( tempSettings, diversity_variation );

            if ( diversity === 'medium' ) {
                tempSettings = moveConnectedFields( tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.5 );
            }

            if ( diversity === 'high' ) {
                tempSettings = moveConnectedFields( tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.67 );
                tempSettings = moveConnectedFields( tempSettings, 'sm_color_secondary', 'sm_color_tertiary', 0.50 );
            }
        }

        var shuffle = $( '[name*="sm_shuffle_colors"]:checked' ).val();
        if ( shuffle !== 'default' ) {
            var shuffle_variation = getSwapMap( 'shuffle_' + shuffle );
            tempSettings = swapConnectedFields( tempSettings, shuffle_variation );
        }

        var dark_mode = $( '[name*="sm_dark_mode"]:checked' ).val();
        if ( dark_mode === 'on' ) {
            var dark_mmode_variation = getSwapMap( 'dark' );
            tempSettings = swapConnectedFields( tempSettings, dark_mmode_variation );
        }

		_.each( masterSettingIds, function( masterSettingId ) {
			wp.customize.settings.settings[masterSettingId] = tempSettings[masterSettingId];
		});
    };

    const getPixelsFromColors = function( colors ) {
        var pixels = [];
        _.each( colors, function( color ) {
            pixels.push( hex2rgba( color ) );
        });
        return pixels;
    };

    const getAveragePixel = function( pixels ) {
        var averagePixel = {
            red: 0,
            green: 0,
            blue: 0,
            alpha: 0,
            hue: 0,
            saturation: 0,
            lightness: 0,
            luma: 0
        };

        for ( var i = 0; i < pixels.length; i++ ) {
            var pixel = pixels[i];

            for ( var k in averagePixel ) {
                averagePixel[k] += pixel[k];
            }
        }

        for ( var k in averagePixel ) {
            averagePixel[k] /= pixels.length;
        }

        return averagePixel;
    }

    const applyColorationValueToFields = () => {
        var setting_id = 'sm_coloration_level';
        var setting = wp.customize( setting_id );
        var coloration = $( '[name*="sm_coloration_level"]:checked' ).val();

        if ( typeof $( '[name*="sm_coloration_level"]:checked' ).data( 'default' ) !== 'undefined' ) {

            var sliders = ['sm_dark_color_primary_slider', 'sm_dark_color_secondary_slider', 'sm_dark_color_tertiary_slider'];
            _.each(sliders, function( slider_id ) {
                var slider_setting = customify_settings.settings[ slider_id ];
                wp.customize( slider_id ).set( slider_setting.default );
                $( '#_customize-input-' + slider_id + '_control ').val( slider_setting.default );
            });
        } else {
            var ratio = parseFloat( coloration );
            $( color_sliders_selector ).val( ratio );
        }
        reinitializeConnectedFields();
    };

    const reinitializeConnectedFields = () => {
        reloadConnectedFields();
//        buildColorMatrix();
        unbindConnectedFields();
        bindConnectedFields();
	    refreshCurrentPaletteControl();
	    resetSettings();
    };

	const confirmChanges = ( callback ) => {
        let altered = !! $( '.c-color-palette .color.altered' ).length;
        let confirmed = true;

        if ( altered ) {
            confirmed = confirm( "One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action." );
        }

        if ( ! altered || confirmed ) {
	        if ( typeof callback === 'function' ) {
                callback();
	        }
            return true;
        }

        return false;
    };

    const bindEvents = () => {
	    const paletteControlSelector = '.c-color-palette__control';
	    const $paletteControl = $( paletteControlSelector );
	    const variation = getCurrentVariation();

	    $paletteControl.removeClass( 'active' );
	    $paletteControl.filter( '.variation-' + variation ).addClass( 'active' );

	    $( document ).on( 'click', '.customify_preset.color_palette input', function (e) {
	        if ( ! confirmChanges( onPaletteChange.bind( this ) ) ) {
	            e.preventDefault();
            }
        } );


        $( '[for*="sm_palette_filter"], [for*="sm_coloration_level"], [for*="sm_color_diversity"], [for*="sm_shuffle_colors"], [for*="sm_dark_mode"]' ).on( 'click', function(e) {
	        if ( ! confirmChanges() ) {
	            e.preventDefault();
	        }
        });

        $( '[name*="sm_coloration_level"]' ).on( 'change', applyColorationValueToFields );
        $( '[name*="sm_color_diversity"]' ).on( 'change', reinitializeConnectedFields );
        $( '[name*="sm_shuffle_colors"]' ).on( 'change', reinitializeConnectedFields );
        $( '[name*="sm_dark_mode"]' ).on( 'change', reinitializeConnectedFields );
        $( '[name*="sm_palette_filter"]' ).on( 'change', () => {
	        updateFilteredColors();
            reinitializeConnectedFields();
        } );

	    $( document ).on( 'click', '.sm-tabs__item', function( e ) {
		    e.preventDefault();

		    var $section = $( '#sub-accordion-section-sm_color_palettes_section' );
		    var $tabs = $( '.sm-tabs__item' );
		    var $active = $(this);
		    var target = $active.data('target');

		    $tabs.removeClass( 'sm-tabs__item--active' );
		    $active.addClass( 'sm-tabs__item--active' );
		    $section.removeClass( 'sm-view-palettes sm-view-filters sm-view-customize').addClass( 'sm-view-' + target );
	    } );

	    $( '.sm-tabs__item' ).first().trigger( 'click' );
    };

    const updateFilterPreviews = _.debounce(() => {
        $( '.sm-palette-filter' ).each(function() {
           let $filters = $( this ).find( 'input' );

           $filters.each(function(i, obj) {
              let $input = $(obj);
              let $label = $input.next( 'label' );
              let label = $input.val();
              let $colors = $label.find('.color');

              $colors.each( function( j, color ) {
                  let $color = $( color );
                  let setting_id = $color.data( 'setting' );
                  let setting = wp.customize( setting_id );
                  let originalColor = setting();

                  $color.css( 'color', filterColor( originalColor, label ) );
              });
           });
        });
    }, 30);

    wp.customize.bind( 'ready', function() {
	    setupGlobals();

        createCurrentPaletteControls();

	    reloadConnectedFields();
	    updateFilteredColors();
	    unbindConnectedFields();
	    bindConnectedFields();
	    refreshCurrentPaletteControl();
//	    buildColorMatrix();

	    updateFilterPreviews();

	    bindEvents();
    } );

    return {
        masterSettingIds: masterSettingIds
    };

} )( jQuery, window, wp );