import $ from 'jquery';

$( document ).ready( function() {
  $( '#reset_customizer_settings' ).on( 'click', function() {
    let confirm = window.confirm( 'Are you sure you want to do this?' );

    if ( !confirm ) {
      return false;
    }

    $.ajax( {
      url: customify.config.wp_rest.root + 'customify/v1/delete_customizer_settings',
      method: 'POST',
      beforeSend: function( xhr ) {
        xhr.setRequestHeader( 'X-WP-Nonce', customify.config.wp_rest.nonce );
      },
      data: {
        'customify_settings_nonce': customify.config.wp_rest.customify_settings_nonce
      }
    } ).done( function( response ) {
      if ( response.success ) {
        alert( 'Success: ' + response.data );
      } else {
        alert( 'Unfortunately, no luck: ' + response.data );
      }
    } ).error( function( e ) {
      console.log( e );
    } );
  } );

} );
