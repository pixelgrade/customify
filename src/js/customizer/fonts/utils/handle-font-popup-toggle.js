import $ from "jquery";

export const handleFontPopupToggle = () => {
  const $allFontCheckboxes = $( '.js-font-option-toggle' );

  // Close all other font fields popups when opening a font field popup.
  $allFontCheckboxes.on( 'click', ( event ) => {
    $allFontCheckboxes.not( event.target ).prop( 'checked', false );
  } );

  // Make sure that all fonts popups are closed when backing away from a panel or section.
  // @todo This doesn't catch backing with ESC key. For that we should hook on Customizer section and panel events ('collapsed').
  $( '#customize-controls .customize-panel-back, #customize-controls .customize-section-back' ).on( 'click', function() {
    $allFontCheckboxes.prop( 'checked', false )
  } );

}
