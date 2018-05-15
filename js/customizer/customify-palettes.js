( function( $, exports, wp ) {

    const settings = [
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

    const initializeColorPalettes = () => {
        // cache initial settings configuration to be able to update connected fields on variation change
        window.settingsClone = $.extend(true, {}, wp.customize.settings.settings);

        // create a stack of callbacks bound to parent settings to be able to unbind them
        // when altering the connected_fields attribute
        window.connectedFieldsCallbacks = {};
    };

    const updateCurrentPalette = ( label ) => {
        const $palette = $( '.c-palette' );
        const $current = $palette.find( '.colors.current' );
        const $next = $palette.find( '.colors.next' );

        label = label || 'Custom Style';
        $palette.find( '.c-palette__name' ).text( label );

        // apply the last animate set of colors to the "current" color palette
        _.each( settings, function( setting_id ) {
            const color = $next.find( '.' + setting_id ).css( 'color' );
            $current.find( '.' + setting_id ).css( 'color', color );
        });

        // removing the "animate" class will put the "next" color palette out view
        // so we can update the colors in it
        $palette.removeClass( 'animate' );

        // update the colors in the "next" palette with the new values
        _.each( settings, function( setting_id ) {
            const setting = wp.customize( setting_id );

            if ( typeof setting !== "undefined" ) {
                $next.find( '.' + setting_id ).css( 'color', setting() );
            }
        });

        // trigger transition to new color palette
        setTimeout(function() {
            $palette.addClass( 'animate' );
            $palette.find( '.c-palette__control' ).css( 'color', wp.customize( 'sm_color_primary' )() );
        });
    };

    const bindVariationChange = () => {
        const paletteControlSelector = '.c-palette__control';
        const variation = wp.customize( 'sm_color_palette_variation' )();

        $( paletteControlSelector ).removeClass( 'active' );
        $( paletteControlSelector ).filter( '[data-target*="' + variation + '"]' ).addClass( 'active' );

        $( 'body' ).on( 'click', paletteControlSelector, function() {
            let $obj = $( this ),
                $target = $( $obj.data( 'target' ) );

            $obj.siblings( paletteControlSelector ).removeClass( 'active' );
            $obj.addClass( 'active' );
            $target.prop( 'checked', true ).trigger( 'change' );
        } );
    };

    const alterConnectedFields = swapMap => {
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
                } else {
                    newConnectedFields = window.settingsClone[fromArray]['connected_fields'];
                }
                wp.customize.settings.settings[to]['connected_fields'] = newConnectedFields;
            }
        } );
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
        _.each( wp.customize.settings.settings, function( parent_setting_data, parent_setting_id ) {
            let parent_setting = wp.customize( parent_setting_id );
            if ( typeof parent_setting_data.connected_fields !== "undefined" ) {
                connectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback( parent_setting_data, parent_setting_id );
                parent_setting.bind( connectedFieldsCallbacks[parent_setting_id] );
            }
        } );
    };

    const unbindConnectedFields = function() {
        _.each( wp.customize.settings.settings, function( parent_setting_data, parent_setting_id ) {
            let parent_setting = wp.customize( parent_setting_id );
            if ( typeof parent_setting_data.connected_fields !== "undefined" && typeof connectedFieldsCallbacks[parent_setting_id] !== "undefined" ) {
                parent_setting.unbind( connectedFieldsCallbacks[parent_setting_id] );
            }
            delete connectedFieldsCallbacks[parent_setting_id];
        } );
    };

    // alter connected fields of the master colors controls depending on the selected palette variation
    const reloadConnectedFields = () => {
        const setting = wp.customize( 'sm_color_palette_variation' );
        const variation = setting();

        if ( ! window.variations.hasOwnProperty( variation ) ) {
            return;
        }

        unbindConnectedFields();
        alterConnectedFields( variations[variation] );
        bindConnectedFields();
        resetSettings( settings );
    };

    const createCurrentPaletteControls = () => {
        const $palette = $( '.c-palette' );
        const $colors = $palette.find( '.colors.next .color' );

        $colors.each( ( i, obj ) => {
            const $obj = $( obj );
            const setting_id = $obj.data( 'setting' );
            const setting = wp.customize( setting_id );

            $obj.iris( {
                change: ( event, ui ) => {
                    const lastColor = setting();
                    const currentColor = ui.color.toString();

                    if ( 'sm_color_primary' === $obj.data( 'setting' ) ) {
	                    $( '.c-palette__control' ).css( 'color', currentColor );
                    }

                    if ( lastColor !== currentColor ) {
                        $obj.css( 'color', currentColor );
	                    setting.set( currentColor );
                        $palette.find( '.c-palette__name' ).text( 'Custom Style' );
                    }
                }
            } );

            $obj.on( 'click', ( e ) => {
                e.stopPropagation();
                e.preventDefault();

                let hidden = ! $obj.find( '.iris-picker' ).is( ":visible" );

                if ( hidden ) {
                    $colors.not( $obj ).addClass( 'inactive' ).iris( 'hide' );
                    $obj.removeClass( 'inactive' );
                } else {
                    $colors.removeClass( 'inactive' );
                }

                $obj.iris( 'color', $obj.css( 'color' ) );
                $obj.iris( 'toggle' );
            } );
        } );

        $( 'body' ).on( 'click', function() {
            $colors.removeClass( 'inactive' ).iris( 'hide' );
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

    const handleColorPalettes = () => {
        initializeColorPalettes();
        createCurrentPaletteControls();
        reloadConnectedFields();
        updateCurrentPalette();
        bindVariationChange();

        // when variation is changed reload connected fields from cached version of customizer settings config
        $( document ).on( 'change', '[name="_customize-radio-sm_color_palette_variation_control"]', reloadConnectedFields );
        $( document ).on( 'click', '.customify_preset.color_palette input', onPaletteChange );
    };

    wp.customize.bind( 'ready', handleColorPalettes );

} )( jQuery, window, wp );