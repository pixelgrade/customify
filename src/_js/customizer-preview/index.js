import {
  getFontFieldCSSValue,
  getFontFieldCSSCode,
  maybeLoadFontFamily
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
    const settings = customify.config.settings;
    const getStyleTagID = ( settingID => `dynamic_style_${ settingID.replace( /\\W/g, '_' ) }` );

    const properKeys = Object.keys( settings ).filter( settingID => {
      const setting = settings[settingID];
      return setting.type === 'font' || ( Array.isArray( setting.css ) && setting.css.length );
    } );

    properKeys.forEach( settingID => {
      const style = document.createElement( 'style' );
      const idAttr = getStyleTagID( settingID );

      style.setAttribute( 'id', idAttr );
      document.body.appendChild( style );
    } );

    // we create a queue of settingID => newValue pairs
    let updateQueue = {};

    // so we can update their respective style tags in only one pass
    // and avoid multiple "recalculate styles" and all changes will appear
    // at the same time in the customizer preview
    const onChange = _.debounce( () => {
      const queue = Object.assign( {}, updateQueue );
      updateQueue = {};

      Object.keys( queue ).forEach( settingID => {
        const idAttr = getStyleTagID( settingID );
        const style = document.getElementById( idAttr );
        const newValue = queue[ settingID ];
        const settingConfig = settings[ settingID ];

        style.innerHTML = getSettingCSS( settingID, newValue, settingConfig );
      } );
    }, 100 );

    properKeys.forEach( settingID => {
      wp.customize( settingID, setting => {
        setting.bind( ( newValue ) => {
          updateQueue[ settingID ] = newValue;
          onChange();
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
    maybeLoadFontFamily( newValue, settingID )
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
