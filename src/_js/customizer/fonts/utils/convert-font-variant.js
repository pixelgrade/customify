/**
 * Will convert an array of CSS like variants into their FVD equivalents. Web Font Loader expects this format.
 * @link https://github.com/typekit/fvd
 */
export const convertFontVariantToFVD = function( variant ) {
  variant = String( variant )

  let fontStyle = 'n' // normal
  if ( - 1 !== variant.indexOf( 'italic' ) ) {
    fontStyle = 'i'
    variant = variant.replace( 'italic', '' )
  } else if ( - 1 !== variant.indexOf( 'oblique' ) ) {
    fontStyle = 'o'
    variant = variant.replace( 'oblique', '' )
  }

  let fontWeight

//  The equivalence:
//
//			1: 100
//			2: 200
//			3: 300
//			4: 400 (default, also recognized as 'normal')
//			5: 500
//			6: 600
//			7: 700 (also recognized as 'bold')
//			8: 800
//			9: 900

  switch ( variant ) {
    case '100':
      fontWeight = '1'
      break
    case '200':
      fontWeight = '2'
      break
    case '300':
      fontWeight = '3'
      break
    case '500':
      fontWeight = '5'
      break
    case '600':
      fontWeight = '6'
      break
    case '700':
    case 'bold':
      fontWeight = '7'
      break
    case '800':
      fontWeight = '8'
      break
    case '900':
      fontWeight = '9'
      break
    default:
      fontWeight = '4'
      break
  }

  return fontStyle + fontWeight
}
