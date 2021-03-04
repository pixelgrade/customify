import $ from 'jquery';

export const handleColorSelectFields = () => {
  $( '.js-color-select' ).each( function( i, obj ) {
    convertToColorSelect( obj );
  } );
}

export const convertToColorSelect = ( element ) => {
  const $select = $( element );
  const $selectOptions = $select.find( 'option' );
  const $colorSelect = $( '<div class="customify-color-select">' );
  const settingID = $select.data( 'customize-setting-link' );

  const $optionsList = $( '<div class="customify-color-select__option-list">' );

  $selectOptions.each( function( i, option ) {
    const $option = $( option );
    const label = $option.text();
    const value = $option.attr( 'value' );
    const $colorSelectOptionLabel = $( '<div class="customify-color-select__option-label">' );
    const $colorSelectOption = $( '<div class="customify-color-select__option">' );

    $colorSelectOptionLabel.text( label ).appendTo( $colorSelectOption );
    $colorSelectOption.data( 'value', value ).appendTo( $optionsList );
    $colorSelectOption.addClass( 'customify-color-select__option--' + value );
  } );

  $optionsList.appendTo( $colorSelect );

  const $colorSelectOptions = $colorSelect.find( '.customify-color-select__option' );

  $colorSelectOptions.each( function( i, option ) {
    const $colorSelectOption = $( option );
    const value = $colorSelectOption.data( 'value' );

    $colorSelectOption.on( 'click', function() {
      $select.val( value ).change();
    } );
  } );

  $colorSelect.insertBefore( $select );
  $select.hide();

  function updateColorSelect( newValue ) {

    const $colorSelectOption = $colorSelectOptions.filter( ( index, obj ) => {
      return $( obj ).data( 'value' ) === newValue;
    } );

    if ( $colorSelectOption.length ) {
      $colorSelectOptions.removeClass( 'customify-color-select__option--selected' );
      $colorSelectOption.addClass( 'customify-color-select__option--selected' );
    }
  }

  wp.customize( settingID, ( setting ) => {
    updateColorSelect( setting() );
    
    setting.bind( updateColorSelect );
  } );
}
