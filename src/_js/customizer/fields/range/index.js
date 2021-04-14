import $ from 'jquery';

export const handleRangeFields = () => {

  const rangeControlSelectors = [
    `.accordion-section-content[id*="${ customify.config.options_name }"]`,
    `#sub-accordion-section-sm_color_palettes_section`,
    `#sub-accordion-section-sm_color_usage_section`,
  ];

  const rangeControlSelector = rangeControlSelectors.join( ', ' );

  $( rangeControlSelector ).each( function ( i, container ) {
    const $rangeFields = $( container ).find( 'input[type="range"]' );

    // For each range input add a number field (for preview mainly - but it can also be used for input)
    $rangeFields.each( function( i, obj ) {
      const $range = $( obj )
      const settingID = $range.data( 'customize-setting-link' );
      const $number = $range.clone();

      $number.attr( 'type', 'text' ).attr( 'class', 'range-value' ).removeAttr( 'data-value_entry' );
      $number.data( 'source', $range );

      if ( $range.first().attr( 'id' ) ) {
        $number.attr( 'id', $range.first().attr( 'id' ) + '_number' );
      }

      $number.insertAfter( $range );

      wp.customize( settingID, setting => {
        setting.bind( newValue => {
          $number.val( newValue );
        } );
      } );

      // When clicking outside the number field or on Enter.
      $number.on( 'blur keyup', onRangePreviewBlur );

    } );
  } );
}

function onRangePreviewBlur( event ) {
  const $number = $( event.target );
  const $range = $number.data( 'source' );

  if ( 'keyup' === event.type && event.keyCode !== 13 ) {
    return
  }

  if ( event.target.value === $range.val() ) {
    // Nothing to do if the values are identical.
    return;
  }

  if ( ! hasValidValue( $number ) ) {
    $number.val( $range.val() );
    shake( $number );
  } else {
    // Do not mark this trigger as being programmatically triggered by Customify since it is a result of a user input.
    $range.val( $number.val() ).trigger( 'change' );
  }
}

function hasValidValue( $input ) {
  const min = $input.attr( 'min' );
  const max = $input.attr( 'max' );
  const value = $input.val();

  if ( typeof min !== 'undefined' && parseFloat( min ) > parseFloat( value ) ) {
    return false
  }

  if ( typeof max !== 'undefined' && parseFloat( max ) < parseFloat( value ) ) {
    return false;
  }

  return true;
}

function shake( $field ) {
  $field.addClass( 'input-shake input-error' )
  $field.one( 'animationend', function() {
    $field.removeClass( 'input-shake input-error' )
  } )
}
