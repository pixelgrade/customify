let ColorPalettes = ( function( $, exports, wp ) {

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

	const hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");

	function rgb2hex(rgb) {
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}

	function hex(x) {
		return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
	}

    const updateCurrentPalette = ( label ) => {
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

        // trigger transition to new color palette
        setTimeout(function() {
            $palette.addClass( 'animate' );
            $palette.find( '.c-color-palette__control' ).css( 'color', wp.customize( 'sm_color_primary' )() );
        });
    };

    const bindVariationChange = () => {
        const paletteControlSelector = '.c-color-palette__control';
        const $paletteControl = $( paletteControlSelector );
        const variation = wp.customize( 'sm_color_palette_variation' )();

        if ( _.isUndefined( variation ) || ! $paletteControl.length ) {
            return;
        }

        $paletteControl.removeClass( 'active' );
        $paletteControl.filter( '.variation-' + variation ).addClass( 'active' );
        $( 'body' ).on( 'click', paletteControlSelector, function() {
            let $obj = $( this ),
                $target = $( $obj.data( 'target' ) );

            $obj.siblings( paletteControlSelector ).removeClass( 'active' );
            $obj.addClass( 'active' );
            $target.prop( 'checked', true ).trigger( 'change' );
        } );
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
            $('.c-color-palette .color').addClass('hidden').filter(optionsSelector).removeClass('hidden');
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

    const getConnectedFieldsCallback = function( parent_setting_data, parent_setting_id ) {
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

                if (typeof parent_setting_data.connected_fields !== "undefined") {
                    connectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback(parent_setting_data, parent_setting_id);
                    parent_setting.bind(connectedFieldsCallbacks[parent_setting_id]);
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

    // alter connected fields of the master colors controls depending on the selected palette variation
    const reloadConnectedFields = () => {
        const setting = wp.customize( 'sm_color_palette_variation' );

        if ( _.isUndefined( setting ) ) {
            return;
        }

        const variation = setting();

        if ( ! window.colorPalettesVariations.hasOwnProperty( variation ) ) {
            return;
        }

        unbindConnectedFields();
        alterConnectedFields( colorPalettesVariations[variation] );
        bindConnectedFields();
    };

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

                    if ( 'sm_color_primary' === $obj.data( 'setting' ) ) {
	                    $( '.c-color-palette__control' ).css( 'color', currentColor );
                    }

                    if ( lastColor !== currentColor ) {
                        $obj.css( 'color', currentColor );
	                    setting.set( currentColor );
                        $palette.find( '.c-color-palette__name' ).text( 'Custom Style' );
                    }
                }
            } );

	        $obj.find( '.iris-picker' ).on( 'click', function( e ) {
		        e.stopPropagation();
		        e.preventDefault();
            } );

	        $obj.on( 'click', ( e ) => {
                e.stopPropagation();
                e.preventDefault();

                if ( $input.is( ':visible' ) ) {
                    $input.iris( 'hide' );
                    $input.hide();
                    $colors.removeClass( 'active inactive' );
                } else {
                    $colors.not( $obj ).each( function( i, obj ) {
                        $( obj ).data( 'target' ).not( $input ).hide();
                    } );
                    $input.show().focus();
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

                $colors.not( $obj ).each( function( i, obj ) {
                    $( obj ).data( 'target' ).iris( 'hide' );
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
    };

    const onPaletteChange = function() {
        const $label = $( this ).next( 'label' ).clone();
        let label;

        $label.find( '.preview__letter' ).remove();
        label = $label.text();
        $label.remove();

        $( this ).trigger( 'customify:preset-change' );
        updateCurrentPalette( label );
    };

    const buildColorMatrix = () => {
        const $matrix = $( '.sm_color_matrix' );

        $matrix.empty();

	    _.each( masterSettingIds, function( setting_id ) {
	        const $bucket = $( '<div class="' + setting_id + '">' ).appendTo( $matrix );
            const color = wp.customize( setting_id )();
		    _.each( wp.customize.settings.settings[setting_id]['connected_fields'], function( connected_field ) {
		        const $color = $( '<div title="' + connected_field.setting_id + '">' ).appendTo( $bucket );
		        $color.css( 'color', color );
            } );
	    });
    };

    const moveConnectedFields = ( settings, from, to, ratio ) => {

	    if ( ! _.isUndefined( settings[to] ) &&
	         ! _.isUndefined( settings[from] ) &&
	         ! _.isUndefined( settings[to]['connected_fields'] ) &&
	         ! _.isUndefined( settings[from]['connected_fields'] ) ) {

		    const oldFromConnectedFields = Object.values( settings[from]['connected_fields'] );
		    const oldToConnectedFields = Object.values( settings[to]['connected_fields'] );
		    const oldConnectedFields = oldToConnectedFields.concat( oldFromConnectedFields );
		    const count = ratio * oldConnectedFields.length;

		    let newToConnectedFields = oldConnectedFields.slice( 0, count );
		    let newFromConnectedFields = oldConnectedFields.slice( count );

		    settings[to]['connected_fields'] = newToConnectedFields;
		    settings[from]['connected_fields'] = newFromConnectedFields;
	    }

	    return settings;
	};

    const handlePalettes = () => {
        initializePalettes();
        createCurrentPaletteControls();
	    reloadConnectedFields();
        updateCurrentPalette();
        bindVariationChange();

        // when variation is changed reload connected fields from cached version of customizer settings config
        $( document ).on( 'change', '[name="_customize-radio-sm_color_palette_variation_control"]', function() {
            reloadConnectedFields();
	        resetSettings( masterSettingIds );
        });

        $( document ).on( 'click', '.customify_preset.color_palette input', onPaletteChange );

	    const $darkColorMaster = $('input[id*="sm_dark_color_master_slider"]');
        const $darkColorPrimary = $('input[id*="sm_dark_color_primary_slider"]');
        const $darkColorSecondary = $('input[id*="sm_dark_color_secondary_slider"]');
        const $darkColorTertiary = $('input[id*="sm_dark_color_tertiary_slider"]');

        const $sliders = $darkColorPrimary.add( $darkColorSecondary ).add( $darkColorTertiary );

	    buildColorMatrix();

	    $darkColorMaster.on( 'input', function() {
	        $sliders.val( $darkColorMaster.val() ).trigger( 'input' );
	    } );

	    const onSliderChange = () => {
		    const primaryRatio = $darkColorPrimary.val() / 100;
		    const secondaryRatio = $darkColorSecondary.val() / 100;
		    const tertiaryRatio = $darkColorTertiary.val() / 100;

		    let tempSettings = window.settingsClone;

		    unbindConnectedFields();

		    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio );
		    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio );
		    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio );

		    _.each( masterSettingIds, function( setting_id ) {
			    wp.customize.settings.settings[setting_id]['connected_fields'] = tempSettings[setting_id]['connected_fields'];
		    } );

		    bindConnectedFields();
		    buildColorMatrix();
		    resetSettings( masterSettingIds );
	    };

	    $sliders.on( 'input', onSliderChange );
    };

    wp.customize.bind( 'ready', handlePalettes );

    return {
        masterSettingIds: masterSettingIds
    };

} )( jQuery, window, wp );