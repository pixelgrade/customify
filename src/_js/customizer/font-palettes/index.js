import $ from 'jquery';

export const initializeFontPalettes = () => {

  $( '.js-font-palette' ).each( function( i, obj ) {
    const $paletteSet = $( obj );
    const $labels = $paletteSet.find( 'label' );

    $labels.on( 'click', function( event ) {
      const $label = $( event.target );
      const forID = $label.attr( 'for' );
      const $input = $( `#${ forID }` );
      const fontsLogic = $input.data( 'fonts_logic' );

      showAdvancedFontPaletteControls();
      applyFontPalette( fontsLogic );
    } );
  } );

  // Handle the case when there is no selected font palette (like on a fresh installation without any demo data import).
  // In this case we want to hide the advanced tab.
  wp.customize( 'sm_font_palette', setting => {
    if ( ! setting() ) {
      hideAdvancedFontPaletteControls();
    }
  } );
}

const applyFontPalette = ( fontsLogic ) => {
  $.each( fontsLogic, ( settingID, config ) => {
    wp.customize( settingID, setting => {
      setting.set( config );
    } );
  } );
}

const advancedTabSelector = '#sub-accordion-section-sm_font_palettes_section .sm-tabs__item[data-target="advanced"]';

const hideAdvancedFontPaletteControls = () => {
  $( advancedTabSelector ).css( 'visibility', 'hidden' );
}

const showAdvancedFontPaletteControls = () => {
  $( advancedTabSelector ).css( 'visibility', 'visible' );
}
