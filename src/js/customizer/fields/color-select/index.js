import $ from 'jquery';

export const handleColorSelectFields = () => {
  $( '.js-color-select' ).each( function( i, obj ) {
    convertToColorSelect( obj );
  } );
}

export const convertToColorSelect = ( element ) => {
  var $select = $( element );
  var $selectOptions = $select.find( 'option' );
  var $colorSelect = $( '<div class="customify-color-select">' );

  var $optionsList = $( '<div class="customify-color-select__option-list">' );

  $selectOptions.each( function( i, option ) {
    var $option = $( option );
    var label = $option.text();
    var value = $option.attr( 'value' );
    var $colorSelectOptionLabel = $( '<div class="customify-color-select__option-label">' );
    var $colorSelectOption = $( '<div class="customify-color-select__option">' );

    $colorSelectOptionLabel.text( label ).appendTo( $colorSelectOption );
    $colorSelectOption.data( 'value', value ).appendTo( $optionsList );
    $colorSelectOption.addClass( 'customify-color-select__option--' + value );
  } );

  $optionsList.appendTo( $colorSelect );

  var $colorSelectOptions = $colorSelect.find( '.customify-color-select__option' );

  $colorSelectOptions.each( function( i, option ) {
    var $colorSelectOption = $( option );
    var value = $colorSelectOption.data( 'value' );

    $colorSelectOption.on( 'click', function() {
      $select.val( value ).change();
    } );
  } );

  $colorSelect.insertBefore( $select );
  $select.hide();

  function updateColorSelect() {
    var value = $select.val();
    var $colorSelectOption = $colorSelectOptions.filter( function( index, obj ) {
      return $( obj ).data( 'value' ) === value;
    } );

    if ( $colorSelectOption.length ) {
      $colorSelectOptions.removeClass( 'customify-color-select__option--selected' );
      $colorSelectOption.addClass( 'customify-color-select__option--selected' );
    }
  }

  updateColorSelect();

  $select.on( 'change', updateColorSelect );
}
