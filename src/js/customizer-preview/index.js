import {
  getFontFieldCSSValue,
  getFontFieldCSSCode
} from './utils';

;(function ($, window, document) {

  $( window ).on( 'load', function() {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript();
  } );

  window.fontsCache = [];

  window.wp = window?.wp || parent?.wp;
  window.customify = window?.customify || parent?.customify;

  $( function() {
    $.each( customify.config.settings, ( settingID, settingConfig ) => {
      wp.customize( settingID, setting => {
        const style = document.createElement( 'style' );
        const idAttr = `dynamic_style_${ settingID.replace( /\\W/g, '_' ) }`;

        style.setAttribute( 'id', idAttr );
        document.body.appendChild( style );

        setting.bind( newValue => {
          style.innerHTML = getSettingCSS( settingID, newValue, settingConfig );
        } );
      } );
    } );
  } );

})(jQuery, window, document);

const maybeLoadWebfontloaderScript = function() {
  if ( typeof WebFont === 'undefined' ) {
    let tk = document.createElement( 'script' );
    tk.src = parent.customify.config.webfontloader_url;
    tk.type = 'text/javascript';
    let s = document.getElementsByTagName( 'script' )[0];
    s.parentNode.insertBefore( tk, s );
  }
}

const defaultCallbackFilter = ( value, selector, property, unit = '' ) => {
  return `${ selector } { ${ property }: ${ value }${ unit }; }`;
}

const getSettingCSS = ( settingID, newValue, settingConfig ) => {

  if ( settingConfig.type === 'font' ) {
    const cssValue = getFontFieldCSSValue( settingID, newValue )
    return getFontFieldCSSCode( settingID, cssValue, newValue );
  }

  if ( ! Array.isArray( settingConfig.css ) ) {
    return '';
  }

  return settingConfig.css.reduce( ( acc, propertyConfig, index ) => {
    const { callback_filter, selector, property, unit } = propertyConfig;
    const settingCallback = callback_filter && typeof window[callback_filter] === "function" ? window[callback_filter] : defaultCallbackFilter;

    if ( ! selector || ! property ) {
      return acc;
    }

    return `${ acc }
      ${ settingCallback( newValue, selector, property, unit ) }`
  }, '' );
}
