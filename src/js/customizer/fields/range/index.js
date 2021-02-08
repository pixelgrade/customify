import $ from 'jquery';

export const handleRangeFields = () => {

  const rangeControlSelector = `.accordion-section-content[id*="${ customify.config.options_name }"], #sub-accordion-section-style_manager_section`;

  $( rangeControlSelector ).each( function ( i, container ) {
    const $rangeFields = $( container ).find( 'input[type="range"]' );

    // For each range input add a number field (for preview mainly - but it can also be used for input)
    $rangeFields.each( function( i, obj ) {
      const $range = $( obj )
      let $number = $range.siblings( '.range-value' )

      if ( ! $number.length ) {
        $number = $range.clone();

        $number.attr( 'type', 'number' ).attr( 'class', 'range-value' ).removeAttr( 'data-value_entry' );
        $number.data( 'source', $range );

        if ( $range.first().attr( 'id' ) ) {
          $number.attr( 'id', $range.first().attr( 'id' ) + '_number' )
        }

        $number.insertAfter( $range )
      }

      // Put the value into the number field.
      $range.on( 'input change', function( event ) {
        if ( event.target.value === $number.val() ) {
          // Nothing to do if the values are identical.
          return;
        }

        $number.val( event.target.value );
      } )

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
