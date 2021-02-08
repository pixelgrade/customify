/**
 * Set a setting value.
 *
 * Mostly used for resetting settings (via the reset buttons) but also for the preset (legacy) field.
 *
 * @param settingID
 * @param value
 */
export const apiSetSettingValue = ( settingID, value ) => {
  const setting = api( settingID ),
    field = $( '[data-customize-setting-link="' + settingID + '"]' ),
    fieldClass = $( field ).parent().attr( 'class' )

  if ( !_.isUndefined( fieldClass ) && fieldClass === 'font-options__wrapper' ) {

    // if the value is a simple string it must be the font family
    if ( _.isString( value ) ) {
      setting.set( {'font_family': value} )
    } else if ( _.isObject( value ) ) {
      const standardValue = {}
      // We will process each font property and update it
      _.each( value, function( val, key ) {
        // We need to map the keys to the data attributes we are using - I know :(
        let mappedKey = key
        switch ( key ) {
          case 'font-family':
            mappedKey = 'font_family'
            break
          case 'font-size':
            mappedKey = 'font_size'
            break
          case 'font-weight':
            mappedKey = 'font_variant'
            break
          case 'letter-spacing':
            mappedKey = 'letter_spacing'
            break
          case 'text-transform':
            mappedKey = 'text_transform'
            break
          default:
            break
        }

        standardValue[mappedKey] = val
      } )

      setting.set( standardValue )
    }
  } else {
    setting.set( value )
  }
}
