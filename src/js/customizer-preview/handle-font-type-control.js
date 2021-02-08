import {
  getFontFieldCSSCode,
  maybeLoadFontFamily
} from './utils';

export const handleFontTypeControl = ( key, settingConfig ) => {
  const wp = wp || parent.wp;

  wp.customize( key, ( setting ) => {

    setting.bind( ( newValue ) => {

      if ( typeof newValue === 'undefined' ) {
        return
      }

      if ( typeof newValue.font_family !== 'undefined' ) {
        maybeLoadFontFamily( newValue, this.id )
      }

      const $styleElement = $( '#customify_font_output_for_' + settingConfig.html_safe_option_id );

      if ( !$styleElement.length ) {
        return
      }

      const cssValue = getFontFieldCSSValue( this.id, newValue )

      if ( _.isEmpty( cssValue ) ) {
        // Empty the style element.
        $styleElement.html( '' )
        return
      }

      $styleElement.html( getFontFieldCSSCode( this.id, cssValue, propertiesPrefix, newValue ) );
    } );
  } );
}
