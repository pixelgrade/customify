import _ from "lodash";
import $ from "jquery";

import {
  getCurrentPaletteColors,
  getFilteredColor,
} from "./index";

const updateFilterPreviews = _.debounce( () => {
  const colors = getCurrentPaletteColors();

  $( '.sm-palette-filter' ).each( function() {
    const $filters = $( this ).find( 'input' );

    $filters.each( function( i, obj ) {
      const $input = $( obj );
      const $label = $input.next( 'label' );
      const label = $input.val();
      const $colors = $label.find( '.sm-color-palette__color' );

      $colors.each( function( j, color ) {
        const $color = $( color );
        const settingID = $color.data( 'setting' );
        const setting = wp.customize( settingID );
        const originalColor = setting();

        $color.css( 'color', getFilteredColor( originalColor, label, colors ) );
      } );
    } );
  } );
}, 30 )

export { updateFilterPreviews };
