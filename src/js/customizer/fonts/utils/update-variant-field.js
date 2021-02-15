import $ from "jquery";
const fontVariantSelector = '.customify_font_weight';

/**
 * This function updates the data in font weight selector from the given <option> element
 *
 * @param newFontDetails
 * @param wrapper
 */
export const updateVariantField = function( newFontDetails, wrapper ) {
  const variants = typeof newFontDetails.variants !== 'undefined' ? newFontDetails.variants : [],
    fontVariantInput = wrapper.find( fontVariantSelector ),
    selectedVariant = fontVariantInput.val() ? fontVariantInput.val() : '',
    newVariants = []

  // We clear everything about this subfield.
  fontVariantInput.val( null ).empty()
  if ( fontVariantInput.hasClass( "select2-hidden-accessible" ) ) {
    fontVariantInput.select2( 'destroy' )
  }

  // Mark this input as not touched by the user.
  fontVariantInput.data( 'touched', false )

  if ( typeof variants === 'undefined' || Object.keys( variants ).length < 2 ) {
    fontVariantInput.parent().hide()
    fontVariantInput.parent().prev( 'label' ).hide()
    // Mark this input as disabled.
    fontVariantInput.data( 'disabled', true )
    return
  }

  const variantAutoText = customify.l10n.fonts.variantAutoText

  // Initialize the options with an empty one.
  newVariants.push( {
    'id': '',
    'text': variantAutoText
  } )

  // we need to turn the data array into a specific form like [{id:"id", text:"Text"}]
  $.each( variants, function( index, variant ) {
    let newVariant = {
      'id': variant, // This is the option value.
      'text': variant
    }

    // Leave the comparison loose.
    if ( selectedVariant == variant ) {
      newVariant.selected = true
    }

    newVariants.push( newVariant )
  } )

  // This is a costly operation especially when font palettes are changed and multiple font fields are updated
  requestIdleCallback( () => {
    // Only reinitialize the select2.
    // No need to rebind on change or on input since those are still bound to the original HTML element.
    fontVariantInput.select2( {
      data: newVariants
    } )

    fontVariantInput.parent().show()
    fontVariantInput.parent().prev( 'label' ).show()
    // Mark this input as enabled.
    fontVariantInput.data( 'disabled', false )
  } );
}
