import { determineFontType } from './determine-font-type';

export const getFontDetails = function( fontFamily, fontType = false ) {
  if ( false === fontType ) {
    // We will determine the font type based on font family.
    fontType = determineFontType( fontFamily )
  }

  switch ( fontType ) {
    case 'theme_font':
      return customify.fonts.theme_fonts[fontFamily]
      break
    case 'cloud_font':
      return customify.fonts.cloud_fonts[fontFamily]
      break
    case 'google_font':
      return customify.fonts.google_fonts[fontFamily]
      break
    case 'system_font':
      if ( typeof customify.fonts.system_fonts[fontFamily] !== 'undefined' ) {
        return customify.fonts.system_fonts[fontFamily]
      }
      break
    default:
  }

  return false
}
