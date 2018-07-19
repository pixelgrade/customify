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

//	const master_color_selector = '#_customize-input-sm_dark_color_master_slider_control';
//	const primary_color_selector = '#_customize-input-sm_dark_color_primary_slider_control';
//	const secondary_color_selector = '#_customize-input-sm_dark_color_secondary_slider_control';
//	const tertiary_color_selector = '#_customize-input-sm_dark_color_tertiary_slider_control';
//	const color_dispersion_selector = '#_customize-input-sm_colors_dispersion_control';
//	const color_focus_point_selector = '#_customize-input-sm_colors_focus_point_control';
//	const color_sliders_selector = primary_color_selector + ', ' + secondary_color_selector + ', ' + tertiary_color_selector;
//	const all_sliders_selector = color_sliders_selector + ', ' + color_dispersion_selector + ', ' + color_focus_point_selector;

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
        if ( typeof window.connectedFieldsCallbacks === "undefined" ) {
            window.connectedFieldsCallbacks = {};
        }

        setupGlobalsDone = true;
    };

	const hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];

	function rgb2hex(rgb) {
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}

	function hex(x) {
		return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
	}

    const setCurrentPalette = ( label ) => {
        const $palette = $( '.c-color-palette' );

        if ( ! $palette.length ) {
            return;
        }

        const $current = $palette.find( '.colors.current' );
        const $next = $palette.find( '.colors.next' );

        label = label || 'Custom Style';
        $palette.find( '.c-color-palette__name' ).text( label );

        // apply the last animate set of colors to the "current" color palette
        _.each( masterSettingIds, function( setting_id ) {
            const color = $next.find( '.' + setting_id ).css( 'color' );
            $current.find( '.color.' + setting_id ).css( 'color', color );
            $palette.find( 'input.' + setting_id ).val( rgb2hex( color ) );
        });

        // removing the "animate" class will put the "next" color palette out view
        // so we can update the colors in it
        $palette.removeClass( 'animate' );

        // update the colors in the "next" palette with the new values
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                $next.find( '.' + setting_id ).css( 'color', setting() );
            }
        });

        $palette.find( '.altered' ).removeClass( 'altered' );
        // trigger transition to new color palette
        setTimeout(function() {
            $palette.addClass( 'animate' );
            updateActiveVariationControlColor();
        });
    };

    const updateActiveVariationControlColor = _.debounce( () => {
        var color = $( '.colors.next .color' ).first( ':visible' ).css( 'color' );
        $( '.c-color-palette__control' ).css( 'color', color );
    }, 30 );

    const resetSettings = () => {
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                let value = setting();
                setting.set( value + "ff" );
                setting.set( value );
            }
        });
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
                setting.set( new_value );
            } );
        }
    };

    const bindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize(parent_setting_id);

                if ( ! _.isUndefined( parent_setting_data.connected_fields ) ) {
                    window.connectedFieldsCallbacks[parent_setting_id] = getMasterFieldCallback(parent_setting_data, parent_setting_id);
                    parent_setting.bind(window.connectedFieldsCallbacks[parent_setting_id]);

                    _.each( parent_setting_data.connected_fields, function( connected_field_data ) {
                        let connected_setting_id = connected_field_data.setting_id;
                        let connected_setting = wp.customize(connected_setting_id);
                        window.connectedFieldsCallbacks[connected_setting_id] = toggleAlteredClassOnMasterControls;
                        connected_setting.bind(window.connectedFieldsCallbacks[connected_setting_id]);
                    } );
                }
            }
        } );
    };

    const unbindConnectedFields = function() {
        _.each( window.connectedFieldsCallbacks, function( callback, setting_id ) {
            let setting = wp.customize(setting_id);
            setting.unbind( callback );
        } );
        window.connectedFieldsCallbacks = {};
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

    // return an array with the hex values of a certain color palette
    const getPaletteColors = ( palette_id ) => {

    }

    // return an array with the hex values of the current palette
    const getCurrentPaletteColors = () => {
        const colors = [];
        _.each( masterSettingIds, function( setting_id ) {
            const setting = wp.customize( setting_id );
            const color = setting();
            colors.push( color );
        } );
        return colors;
    }

    const createCurrentPaletteControls = () => {
        const $palette = $( '.c-color-palette' );
        const $fields = $palette.find( '.c-color-palette__fields' ).find( 'input' );

        if ( ! $palette.length ) {
            return;
        }

        const $colors = $palette.find( '.colors.next .color' );

        $colors.each( ( i, obj ) => {
            const $obj = $( obj );
            const setting_id = $obj.data( 'setting' );
            const $input = $fields.filter( '.' + setting_id );
            const setting = wp.customize( setting_id );

            $obj.data( 'target', $input );

            $input.iris( {
                change: ( event, ui ) => {
                    const lastColor = setting();
                    const currentColor = ui.color.toString();

                    updateActiveVariationControlColor();
                    setPalettesOnConnectedFields();

                    if ( lastColor !== currentColor ) {
                        $obj.css( 'color', currentColor );
	                    setting.set( currentColor );
                        $palette.find( '.c-color-palette__name' ).text( 'Custom Style' );
                    }

                    if ( event.originalEvent.type !== 'external' ) {
                        $palette.find( '.color.' + setting_id ).removeClass( 'altered' );
                    }
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
            }

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

		        $colors.each( ( i, obj ) => {
		            $( obj ).data( 'target' ).not( $input ).iris( 'hide' );
		        } );

		        $colors.not( $obj ).addClass( 'inactive' ).removeClass( 'active' );
		        $obj.addClass( 'active' ).removeClass( 'inactive' );

                $colors.not( $obj ).each( function( i, other ) {
                    $( other ).data( 'target' ).iris( 'hide' );
                } );

                const $iris = $input.next( '.iris-picker' );
                const paletteWidth = $palette.outerWidth();
                const $visibleColors = $colors.filter( ':visible' );
                const index = $visibleColors.index( $obj );

                $iris.css( 'left', ( paletteWidth - 200 ) * index / ( $visibleColors.length - 1 ) );

                $input.iris( 'color', $obj.css( 'color' ) );
                $input.iris( 'show' );
            } );
        } );

        $( 'body' ).on( 'click', function() {
            $colors.removeClass( 'active inactive' );
	        $colors.each( function( i, obj ) {
	            const $input = $( obj ).data( 'target' );

		        $input.iris( 'hide' );
		        $input.hide();
	        } );
        } );

        setCurrentPalette();
    };

    const onPaletteChange = function() {
        const $label = $( this ).next( 'label' ).clone();
        let label;

        $label.find( '.preview__letter' ).remove();
        label = $label.text();
        $label.remove();

        $( this ).trigger( 'customify:preset-change' );
        setCurrentPalette( label );

        setPalettesOnConnectedFields();

//	    buildColorMatrix();
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
                const $bucket = $( '<div class="' + setting_id + '">' ).appendTo( $matrix );
            } );
        }

	    _.each( masterSettingIds, function( setting_id ) {
            const $bucket = $matrix.children( '.' + setting_id );
            const color = wp.customize( setting_id )();
            let classes = [];

            $bucket.css( 'color', color );

		    _.each( wp.customize.settings.settings[setting_id]['connected_fields'], function( connected_field ) {
                const field_id = connected_field.setting_id;
                const fieldClassName = field_id.replace( '[', '_' ).replace( ']', '' );
                classes.push( fieldClassName );

                if ( ! $bucket.children( '.' + fieldClassName ).length ) {
                    const $color = $( '<div title="' + field_id + '" class="' + fieldClassName + '">' ).appendTo( $bucket );
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
                    let connectedFieldValue = wp.customize( connectedSettingId )();
                    if ( connectedFieldValue.toLowerCase() !== masterSettingValue.toLowerCase() ) {
                        connectedFieldsWereAltered = true;
                    }
                } );

                if ( connectedFieldsWereAltered ) {
                    alteredSettings.push( masterSettingId );
                }
            }
        } );

        alteredSettingsSelector = '.' + alteredSettings.join(', .');

        $( '.c-color-palette .color' ).removeClass( 'altered' );
        $( '.js-altered-notification' ).toggleClass( 'hidden', ! alteredSettings.length );

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

        $( '.c-color-palette .color' ).addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' )
        $( '.customify_preset.color_palette .palette__item' ).addClass( 'hidden' ).filter( optionsSelector ).removeClass( 'hidden' );
    }, 30 );

    const refreshCurrentPaletteControl = () => {
        toggleAlteredClassOnMasterControls();
        toggleHiddenClassOnMasterControls();
        updateActiveVariationControlColor();
    }

	const swapConnectedFields = ( settings ) => {
        let variation = getCurrentVariation();
        let swapMap = window.colorPalettesVariations[variation];
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
		    const count = ratio * oldConnectedFields.length;

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

    const disperseColorConnectedFields = ( oldSettings, dispersion, focus ) => {

        let settings = _.clone(oldSettings);

    	if ( _.isUndefined( settings['sm_color_primary']['connected_fields'] ) ) {
		    settings['sm_color_primary']['connected_fields'] = [];
	    }

    	if ( _.isUndefined( settings['sm_color_secondary']['connected_fields'] ) ) {
		    settings['sm_color_secondary']['connected_fields'] = [];
	    }

    	if ( _.isUndefined( settings['sm_color_tertiary']['connected_fields'] ) ) {
		    settings['sm_color_tertiary']['connected_fields'] = [];
	    }

	    const primaryConnectedFields = Object.values( settings['sm_color_primary']['connected_fields'] );
	    const secondaryConnectedFields = Object.values( settings['sm_color_secondary']['connected_fields'] );
	    const tertiaryConnectedFields = Object.values( settings['sm_color_tertiary']['connected_fields'] );

	    //  A1              A2              A3             A4
	    //  |--- primary ---|-- secondary --|-- tertiary --|
	    //          B1                B2
	    //          |----- focus -----|

	    const b1 = Math.max(0, focus - dispersion / 2 );
	    const b2 = Math.min(1, focus + dispersion / 2 );
	    const a1 = 0;
	    const a2 = 0.334;
	    const a3 = 0.667;
	    const a4 = 1;

	    const primaryWidth = b1 > a2 || b2 < a1 ? 0 : Math.min(a2, b2) - Math.max(a1, b1);
	    const secondaryWidth = b1 > a3 || b2 < a2 ? 0 : Math.min(a3, b2) - Math.max(a2, b1);
	    const tertiaryWidth = b1 > a4 || b2 < a3 ? 0 : Math.min(a4, b2) - Math.max(a3, b1);

	    const totalWidth = primaryWidth + secondaryWidth + tertiaryWidth;
	    const connectedFields = primaryConnectedFields.concat( secondaryConnectedFields ).concat( tertiaryConnectedFields );
	    const primaryFieldsCount = Math.round(connectedFields.length * primaryWidth / totalWidth);
	    const secondaryFieldsCount = Math.round(connectedFields.length * secondaryWidth / totalWidth);

	    let newPrimaryConnectedFields = connectedFields.slice(0, primaryFieldsCount);
	    let newSecondaryConnectedFields = connectedFields.slice(primaryFieldsCount, primaryFieldsCount + secondaryFieldsCount);
	    let newTertiaryConnectedFields = connectedFields.slice(primaryFieldsCount + secondaryFieldsCount);

	    newPrimaryConnectedFields = Object.keys( newPrimaryConnectedFields ).map( function(key) {
		    return newPrimaryConnectedFields[key];
	    });
	    newSecondaryConnectedFields = Object.keys( newSecondaryConnectedFields ).map( function(key) {
		    return newSecondaryConnectedFields[key];
	    });
	    newTertiaryConnectedFields = Object.keys( newTertiaryConnectedFields ).map( function(key) {
		    return newTertiaryConnectedFields[key];
	    });

	    settings['sm_color_primary']['connected_fields'] = newPrimaryConnectedFields;
	    settings['sm_color_secondary']['connected_fields'] = newSecondaryConnectedFields;
	    settings['sm_color_tertiary']['connected_fields'] = newTertiaryConnectedFields;

    	return settings;
    };

	const reloadConnectedFields = () => {
//		const primaryRatio = $( primary_color_selector ).val() / 100;
//		const secondaryRatio = $( secondary_color_selector ).val() / 100;
//		const tertiaryRatio = $( tertiary_color_selector ).val() / 100;
//		const colorDispersion = $( color_dispersion_selector ).val() / 100;
//		const focusPoint = $( color_focus_point_selector ).val() / 100;

		let tempSettings = JSON.parse(JSON.stringify(window.settingsClone));

//		tempSettings = moveConnectedFields( tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio );
//		tempSettings = moveConnectedFields( tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio );
//		tempSettings = moveConnectedFields( tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio );
//		tempSettings = disperseColorConnectedFields( tempSettings, colorDispersion, focusPoint );

		tempSettings = swapConnectedFields( tempSettings );
		wp.customize.settings.settings = tempSettings;
	};

    const reinitializeConnectedFields = () => {
        reloadConnectedFields();
        unbindConnectedFields();
        bindConnectedFields();
        resetSettings();
        refreshCurrentPaletteControl();
    }

    const confirmChanges = ( callback ) => {
        if ( typeof callback !== 'function' ) {
            return;
        }

        let altered = !! $( '.c-color-palette .color.altered' ).length;
        let confirmed = true;

        if ( altered ) {
            confirmed = confirm( "One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action." );
        }

        if ( ! altered || confirmed ) {
            callback();
        }
    }


    const bindEvents = () => {
	    const paletteControlSelector = '.c-color-palette__control';
	    const $paletteControl = $( paletteControlSelector );
	    const variation = getCurrentVariation();

	    $paletteControl.removeClass( 'active' );
	    $paletteControl.filter( '.variation-' + variation ).addClass( 'active' );

	    $( 'body' ).on( 'click', paletteControlSelector, function() {
            confirmChanges( () => {
                let $obj = $( this ),
                    $target = $( $obj.data( 'target' ) );

                $obj.siblings( paletteControlSelector ).removeClass( 'active' );
                $obj.addClass( 'active' );
                $target.prop( 'checked', true ).trigger( 'change' );
            } );
	    } );

	    // when variation is changed reload connected fields from cached version of customizer settings config
	    $( document ).on( 'change', '[name="_customize-radio-sm_color_palette_variation_control"]', reinitializeConnectedFields );

	    //
	    $( document ).on( 'click', '.customify_preset.color_palette input', function () {
            confirmChanges( onPaletteChange.bind( this ) );
        } );

//	    $( all_sliders_selector ).on( 'input', reloadConnectedFields );

//	    $( master_color_selector ).on( 'input', function() {
//		    const masterValue = $( master_color_selector ).val();
//		    $( color_sliders_selector ).val( masterValue ).trigger( 'input' );
//	    } );
    };

    wp.customize.bind( 'ready', function() {
	    setupGlobals();

        createCurrentPaletteControls();

//	    buildColorMatrix();
	    reloadConnectedFields();
        bindConnectedFields();
        refreshCurrentPaletteControl();
        setPalettesOnConnectedFields();
	    bindEvents();
    } );

    return {
        masterSettingIds: masterSettingIds
    };

} )( jQuery, window, wp );