import { handleFontTypeControl } from './handle-font-type-control';

;(function ($, window, document) {

  /* global customify.config */
  /* global WebFont */

  Object.defineProperty(String.prototype, 'hashCode', {
    value: function() {
      var hash = 0, i, chr;
      for (i = 0; i < this.length; i++) {
        chr   = this.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
      }
      return hash;
    }
  });

  $( window ).on( 'load', function() {
    // We need to do this on window.load because on document.ready might be too early.
    maybeLoadWebfontloaderScript();
  } );

  const maybeLoadWebfontloaderScript = function() {
    if ( typeof WebFont === 'undefined' ) {
      let tk = document.createElement( 'script' );
      tk.src = parent.customify.config.webfontloader_url;
      tk.type = 'text/javascript';
      let s = document.getElementsByTagName( 'script' )[0];
      s.parentNode.insertBefore( tk, s );
    }
  }

  const fontsCache = [];

  const wp = wp || parent.wp;
  const customify = customify || parent.customify;


  // Do everything at document.ready
  $(function () {
    const api = parent.wp.customize
    const customify = parent.customify
    const apiSettings = api.settings.settings
    const regexForMultipleReplace = new RegExp('-', 'g')

    $.each(customify.config.settings, function (key, settingConfig) {
      const propertiesPrefix = typeof settingConfig.properties_prefix === 'undefined' ? '' : settingConfig.properties_prefix
      if ( settingConfig.type === 'font' ) {
        handleFontTypeControl( key, settingConfig );

      } else {
        if ( typeof apiSettings !== 'undefined'
            && typeof apiSettings[key] !== 'undefined'
            && typeof settingConfig.css !== 'undefined'
            && typeof settingConfig.live !== 'undefined'
            && settingConfig.live === true) {

          api(key, function (setting) {
            setting.bind(function (newValue) {

              $.each(settingConfig.css, function (idx, propertyConfig) {
                // Replace all dashes with underscores thus making the CSS property safe to us in a HTML ID.
                const $styleElement = $('.dynamic_setting_' + settingConfig.html_safe_option_id + '_property_' + propertyConfig.property.replace(regexForMultipleReplace, '_') + '_' + idx)
                if (!$styleElement.length) {
                  return
                }

                const properties = {}
                if (typeof propertyConfig.property !== 'undefined' && typeof propertyConfig.selector !== 'undefined') {
                  properties[propertyConfig.property] = propertyConfig.selector
                }
                if (typeof propertyConfig.callback_filter !== 'undefined') {
                  properties['callback'] = propertyConfig.callback_filter
                }
                if (_.isEmpty(properties)) {
                  return
                }

                const cssUpdateArgs = {
                  properties: properties,
                  propertyValue: newValue,
                  negative_value: propertyConfig.hasOwnProperty('negative_value') ? propertyConfig['negative_value'] : false
                }

                if (typeof this.unit !== 'undefined') {
                  cssUpdateArgs.unit = this.unit
                }

                $styleElement.cssUpdate(cssUpdateArgs)
              })

            })
          })
        } else if (typeof settingConfig.live === 'object' && settingConfig.live.length > 0) {
          // If the live parameter is an object it means that this is a list of css classes.
          // These classes should be affected by the change of the text fields.
          const fieldClass = settingConfig.live.join()

          // if this field is allowed to modify text then we'll edit this live
          if ($.inArray(settingConfig.type, ['text', 'textarea', 'ace_editor']) > -1) {
            api(key, function (value) {
              value.bind(function (text) {
                let sanitizer = document.createElement('div')

                sanitizer.innerHTML = text
                $(fieldClass).html(text)
              })
            })
          }
        }
      }
    })
  });

})(jQuery, window, document)
