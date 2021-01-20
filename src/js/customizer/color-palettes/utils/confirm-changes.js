import $ from "jquery";

export const confirmChanges = ( callback ) => {
  const altered = $( '.c-color-palette .color.altered' ).length
  let confirmed = true

  if ( altered ) {
    confirmed = confirm( 'One or more fields connected to the color palette have been modified. By changing the palette variation you will lose changes to any color made prior to this action.' )
  }

  if ( ! altered || confirmed ) {
    if ( typeof callback === 'function' ) {
      callback()
    }
    return true
  }

  return false
}

const onPaletteChange = function () {
  $( this ).trigger( 'customify:preset-change' );
  reinitializeConnectedFields();
}

const bindConfirmChanges = () => {
  // confirm changes before changing the color palette
  $( document ).on( 'click', '.js-color-palette input', function( e ) {
    if ( ! confirmChanges( onPaletteChange.bind( this ) ) ) {
      e.preventDefault()
    }
  } );

  // confirm changes before changing the color palette
  const controls = [ 'sm_palette_filter', 'sm_coloration_level', 'sm_color_diversity', 'sm_shuffle_colors', 'sm_dark_mode' ];
  const selector = controls.map( name => `[for*="${ name }"]` ).join( ', ' );

  $( document ).on( 'click', selector, function( e ) {
    if ( ! confirmChanges() ) {
      e.preventDefault()
    }
  } );
}
