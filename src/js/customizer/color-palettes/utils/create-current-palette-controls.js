import $ from "jquery";

import {
  confirmChanges,
  updatePalettePreview,
} from "./index";

const createCurrentPaletteControls = () => {
  const $palette = $( '.c-color-palette' );
  const $colors = $palette.find( '.sm-color-palette__color' );
  const $fields = $palette.find( '.c-color-palette__fields' ).find( 'input' );

  if ( ! $palette.length ) {
    return
  }

  $colors.each( ( i, obj ) => {
    const $obj = $( obj );
    const settingID = $obj.data( 'setting' );
    const $input = $fields.filter( '.' + settingID );
    const setting = wp.customize( settingID );

    $obj.data( 'target', $input )

    if ( $obj.hasClass( 'js-no-picker' ) ) {
      return
    }

    const onChange = _.throttle( ( event, ui ) => {

      if ( event.originalEvent.type !== 'external' ) {
        setting.set( ui.color.toString() );
        $palette.find( '.sm-color-palette__color.' + settingID ).removeClass( 'altered' )
      }

    }, 20, { trailing: true } )

    $input.iris( {
      color: setting(),
      change: onChange
    } );

    setting.bind( ( newValue ) => {
      $input.iris( 'color', newValue );
    } );

    $obj.find( '.iris-picker' ).on( 'click', function( e ) {
      e.stopPropagation()
      e.preventDefault()
    } )

    const showColorPicker = () => {
      $colors.not( $obj ).each( function( i, obj ) {
        $( obj ).data( 'target' ).not( $input ).hide()
      } )
      $input.show().focus()
    }

    $obj.on( 'click', ( e ) => {
      e.stopPropagation()
      e.preventDefault()

      if ( $input.is( ':visible' ) ) {
        $input.iris( 'hide' )
        $input.hide()
        $colors.removeClass( 'active inactive' )
      } else {
        if ( $obj.is( '.altered' ) ) {
          confirmChanges( showColorPicker )
        } else {
          showColorPicker()
        }
      }
    } )

    $input.on( 'click', ( e ) => {
      e.stopPropagation()
      e.preventDefault()
    } )

    $input.on( 'focus', ( e ) => {
      $colors.not( $obj ).addClass( 'inactive' ).removeClass( 'active' )
      $obj.addClass( 'active' ).removeClass( 'inactive' )

      $colors.not( $obj ).each( function( i, other ) {
        $( other ).data( 'target' ).iris( 'hide' )
      } )

      const $iris = $input.next( '.iris-picker' )
      const paletteWidth = $palette.find( '.c-color-palette__colors' ).outerWidth()
      const $visibleColors = $colors.filter( ':visible' )
      const index = $visibleColors.index( $obj )

      $iris.css( 'left', ( paletteWidth - 200 ) * index / ( $visibleColors.length - 1 ) );

      updatePalettePreview( false );

      $input.iris( 'show' );
    } )

    $input.on( 'focusout', ( e ) => {
      updatePalettePreview()
    } )
  } );

  updatePalettePreview();

  $( '.c-color-palette__fields' ).on( 'click', '.iris-picker', function( e ) {
    e.stopPropagation();
  } );

  $( 'body' ).on( 'click', function() {
    $colors.removeClass( 'active inactive' )
    $colors.each( function( i, obj ) {
      const $input = $( obj ).data( 'target' )

      if ( ! $( obj ).hasClass( 'js-no-picker' ) ) {
        $input.iris( 'hide' )
      }
      $input.hide()
    } )
  } )
}

export { createCurrentPaletteControls };