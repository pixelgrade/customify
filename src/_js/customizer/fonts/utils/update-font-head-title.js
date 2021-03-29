import $ from "jquery";

/**
 * Update the title of the font field (the field head) with the new font family name.
 *
 * @param newFontDetails
 * @param wrapper
 */
export const updateFontHeadTitle = function( newFontDetails, wrapper ) {
  const fontTitleElement = wrapper.find( '.font-options__head .font-options__font-title' )

  let fontFamilyDisplay = newFontDetails.family
  if ( typeof newFontDetails.family_display === 'string' && newFontDetails.family_display.length ) {
    fontFamilyDisplay = newFontDetails.family_display
  }

  $( fontTitleElement ).html( fontFamilyDisplay )
}
