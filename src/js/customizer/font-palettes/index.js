import $ from 'jquery';

wp.customize.bind( 'ready', () => {
  initializeFontPalettes();
} );

const initializeFontPalettes = () => {

  // Handle the palette change logic.
  $( '.js-font-palette input[name="sm_font_palette"]' ).on( 'change', onPaletteChange )

  // Handle the case where one clicks on the already selected palette - force a reset.
  $( '.js-font-palette .customize-inside-control-row' ).on( 'click', event => {
    // Find the input
    const $target = $( event.target );
    const $input = $target.find( 'input[name="sm_font_palette"]' );

    $input.trigger( 'change' );
  } )

  // Handle the case when there is no selected font palette (like on a fresh installation without any demo data import).
  // In this case we want to hide the advanced tab.
  wp.customize( 'sm_font_palette', setting => {
    if ( ! setting() ) {
      hideAdvancedFontPaletteControls();
    }
  } );
}

const advancedTabSelector = '#sub-accordion-section-sm_font_palettes_section .sm-tabs__item[data-target="advanced"]';

const hideAdvancedFontPaletteControls = () => {
  $( advancedTabSelector ).css( 'visibility', 'hidden' );
}

const showAdvancedFontPaletteControls = () => {
  $( advancedTabSelector ).css( 'visibility', 'visible' );
}

const onPaletteChange = function () {
  // Make sure that the advanced tab is visible.
  showAdvancedFontPaletteControls();

  // Take the fonts config for each setting and distribute it to each (master) setting.
  const fontsLogic = $( this ).data( 'fonts_logic' );

  $.each( fontsLogic, ( settingID, config ) => {
    wp.customize( settingID, setting => {
      setting.set( config );
    } );
  } );
}
