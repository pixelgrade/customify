let FontPalettes = ( function( $, exports, wp ) {

    const masterFontSettings = [
        'sm_font_primary',
        'sm_font_secondary',
        'sm_font_body'
    ];

    const initializeFontPalettes = () => {
        // cache initial settings configuration to be able to update connected fields on variation change
        window.settingsClone = $.extend(true, {}, wp.customize.settings.settings);

        // create a stack of callbacks bound to parent settings to be able to unbind them
        // when altering the connected_fields attribute
        window.connectedFieldsCallbacks = {};
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
        _.each( masterFontSettings, function( setting_id ) {
            const font = $next.find( '.' + setting_id ).css( 'font' );
            $current.find( '.' + setting_id ).css( 'font', font );
        });

        // removing the "animate" class will put the "next" font palette out view
        // so we can update the fonts in it
        $palette.removeClass( 'animate' );

        // update the fonts in the "next" palette with the new values
        _.each( masterFontSettings, function( setting_id ) {
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

    const bindVariationChange = () => {
        const paletteControlSelector = '.c-font-palette__control';
        const $paletteControl = $( paletteControlSelector );
        const variation = wp.customize( 'sm_font_palette_variation' )();

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

                // Process the font logic for the master font control to get the value that should be applied to the connected (font) fields.



                setting.set( new_value );
            } );
        }
    };

    const bindConnectedFields = function() {
        _.each( masterFontSettings, function( parent_setting_id ) {
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
        _.each( masterFontSettings, function( parent_setting_id ) {
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

    // alter connected fields of the master fonts controls depending on the selected palette variation
    const reloadConnectedFields = () => {
        const setting = wp.customize( 'sm_font_palette_variation' );

        if ( _.isUndefined( setting ) ) {
            return;
        }

        const variation = setting();

        if ( ! window.fontPalettesVariations.hasOwnProperty( variation ) ) {
            return;
        }

        unbindConnectedFields();
        alterConnectedFields( fontPalettesVariations[variation] );
        bindConnectedFields();
    };

    const createCurrentPaletteControls = () => {
        const $palette = $( '.c-font-palette' );

        if ( ! $palette.length ) {
            return;
        }

        const $fonts = $palette.find( '.fonts.next .font' );
    };

    const onPaletteChange = function() {
        const $label = $( this ).next( 'label' ).clone();
        let label;

        $label.find( '.preview__letter' ).remove();
        label = $label.text();
        $label.remove();

        // Take the fonts config for each setting and distribute it to each (master) setting.
        const data = $( this ).data( 'fonts_logic' );
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
        const setting = wp.customize( setting_id );
        if ( _.isUndefined( setting ) ) {
            return;
        }

        setting.set( 'sdfsdfsdfsdfsdfsdfsd');
    };

    const handleFontPalettes = () => {
        initializeFontPalettes();
        createCurrentPaletteControls();
	    reloadConnectedFields();
        updateCurrentPalette();
        bindVariationChange();

        // when variation is changed reload connected fields from cached version of customizer settings config
        $( document ).on( 'change', '[name="_customize-radio-sm_font_palette_variation_control"]', function() {
            reloadConnectedFields();
	        resetSettings( masterFontSettings );
        });

        $( document ).on( 'click', '.customify_preset.font_palette input', onPaletteChange );
    };

    wp.customize.bind( 'ready', handleFontPalettes );

    return {
        masterFontSettings: masterFontSettings
    };

} )( jQuery, window, wp );