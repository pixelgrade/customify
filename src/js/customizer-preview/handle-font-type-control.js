import $ from 'jquery';

import {
  getFontFieldCSSValue,
  getFontFieldCSSCode,
  maybeLoadFontFamily
} from './utils';

export const handleFontTypeControl = function( settingID, settingConfig ) {
  const wp = wp || parent.wp;
  const propertiesPrefix = typeof settingConfig.properties_prefix === 'undefined' ? '' : settingConfig.properties_prefix

  wp.customize( settingID, ( setting ) => {
    setting.bind( ( newValue ) => {

      if ( typeof newValue === 'undefined' ) {
        return
      }

      if ( typeof newValue.font_family !== 'undefined' ) {
        maybeLoadFontFamily( newValue, settingID )
      }

      const $styleElement = $( '#customify_font_output_for_' + settingConfig.html_safe_option_id );

      if ( !$styleElement.length ) {
        return
      }

      const cssValue = getFontFieldCSSValue( settingID, newValue )

      if ( _.isEmpty( cssValue ) ) {
        // Empty the style element.
        $styleElement.html( '' )
        return
      }

      console.log( settingID, cssValue, propertiesPrefix, newValue );
      console.log( getFontFieldCSSCode( settingID, cssValue, propertiesPrefix, newValue ) );

      $styleElement.html( getFontFieldCSSCode( settingID, cssValue, propertiesPrefix, newValue ) );
    } );
  } );
}
