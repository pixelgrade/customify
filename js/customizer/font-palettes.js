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
        if ( typeof window.fontsConnectedFieldsCallbacks === "undefined" ) {
            window.fontsConnectedFieldsCallbacks = {};
        }
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
                    	let result = regression.logarithmic( fonts_logic.font_size_to_line_height_points, { precision: 2 } );
                        let fontsize = connected_field_data.font_size.value;
                        let lineheight = result.predict( fontsize )[1];
                        newFontData['line_height'] = { value: lineheight };
                    }
                }

                let serializedNewFontData = CustomifyFontSelectFields.encodeValues(newFontData);
                setting.set(serializedNewFontData);
            });
        }
    };

    const bindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize( parent_setting_id );

                if ( typeof parent_setting_data.connected_fields !== "undefined" ) {
                    fontsConnectedFieldsCallbacks[parent_setting_id] = getConnectedFieldsCallback( parent_setting_data, parent_setting_id );
                    parent_setting.bind( fontsConnectedFieldsCallbacks[parent_setting_id] );
                }
            }
        } );
    };

    const unbindConnectedFields = function() {
        _.each( masterSettingIds, function( parent_setting_id ) {
            if ( typeof wp.customize.settings.settings[parent_setting_id] !== "undefined" ) {
                let parent_setting_data = wp.customize.settings.settings[parent_setting_id];
                let parent_setting = wp.customize(parent_setting_id);

                if (typeof parent_setting_data.connected_fields !== "undefined" && typeof fontsConnectedFieldsCallbacks[parent_setting_id] !== "undefined") {
                    parent_setting.unbind(fontsConnectedFieldsCallbacks[parent_setting_id]);
                }
                delete fontsConnectedFieldsCallbacks[parent_setting_id];
            }
        } );
    };

    // Alter connected fields of the master fonts controls depending on the selected palette variation.
    const reloadConnectedFields = () => {
        unbindConnectedFields();
        bindConnectedFields();
    };

    const onPaletteChange = function() {
        // Take the fonts config for each setting and distribute it to each (master) setting.
        let data = $( this ).data( 'fonts_logic' );

        if ( ! _.isUndefined( data ) ) {
            $.each( data, function( setting_id, config ) {
                set_field_fonts_logic_config( setting_id, config );
            } );
        }

        // In case this palette has values (options) attached to it, let it happen.
        $( this ).trigger( 'customify:preset-change' );
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
	    reloadConnectedFields();

        $( document ).on( 'click', '.customify_preset.font_palette input', onPaletteChange );
    };

    wp.customize.bind( 'ready', handlePalettes );

    return {
        masterSettingIds: masterSettingIds
    };

} )( jQuery, window, wp );