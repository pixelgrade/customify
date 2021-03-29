import $ from 'jquery';

export const scalePreview = function() {

  wp.customize.previewer.bind( 'synced', function() {
    scalePreviewIframe();

    wp.customize.previewedDevice.bind( scalePreviewIframe );
    $( window ).on( 'resize', scalePreviewIframe );
  } )

  $( '.collapse-sidebar' ).on( 'click', function() {
    setTimeout( scalePreviewIframe, 300 );
  } );

}

const scalePreviewIframe = function() {
  const $window = $( window );
  const $previewIframe = $( '.wp-full-overlay' )

  // remove CSS properties that may have been previously added
  $previewIframe.find( 'iframe' ).css( {
    width: '',
    height: '',
    transformOrigin: '',
    transform: ''
  } );

  // scaling of the site preview should be done only in desktop preview mode
  if ( wp.customize.previewedDevice.get() !== 'desktop' ) {
    return
  }

  const iframeWidth = $previewIframe.width()
  const windowWidth = $window.width()
  const windowHeight = $window.height()

  // get the ratio between the site preview and actual browser width
  const scale = windowWidth / iframeWidth

  // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the iframe and use CSS transforms to scale it back down
  if ( iframeWidth > 720 && iframeWidth < 1100 ) {
    $previewIframe.find( 'iframe' ).css( {
      width: iframeWidth * scale,
      height: windowHeight * scale,
      transformOrigin: 'left top',
      transform: 'scale(' + 1 / scale + ')'
    } );
  }
}
