let offset = {
  top: 0,
  right: 0,
  bottom: 0,
  left: 0,
};

wp.customize.bind( 'ready', () => {

  setOffset( {
    top: 10,
    right: 10,
    bottom: 10,
    left: 10,
  } );

  resize();

  window.addEventListener( 'resize', resize );

  wp.customize.previewedDevice.bind( resize );

  const collapseSidebar = document.querySelector( '.collapse-sidebar' );

  if ( ! collapseSidebar ) {
    return;
  }

  collapseSidebar.addEventListener( 'click', () => {
    setTimeout( resize, 300 );
  } );

} );

export const setOffset = ( newOffset ) => {
  offset = Object.assign( {}, newOffset );
}

export const resize = () => {

  const iframe = document.querySelector( '#customize-preview iframe' );

  if ( ! iframe ) {
    return;
  }

  // remove CSS properties that may have been previously added
  iframe.style.removeProperty( 'width' );
  iframe.style.removeProperty( 'height' );
  iframe.style.removeProperty( 'transformOrigin' );
  iframe.style.removeProperty( 'transform' );

  iframe.style.removeProperty( 'marginTop' );
  iframe.style.removeProperty( 'marginLeft' );

  // scaling of the site preview should be done only in desktop preview mode
  if ( wp.customize.previewedDevice.get() !== 'desktop' ) {
    return
  }

  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;

  const iframeWidth = iframe.offsetWidth - offset.left - offset.right;
  const iframeHeight = windowHeight - offset.top - offset.bottom;

  // get the ratio between the site preview and actual browser width
  const scale = windowWidth / iframeWidth;

  // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the iframe and use CSS transforms to scale it back down
  if ( iframeWidth > 720 && iframeWidth < 1100 ) {
    iframe.style.width = `${ iframeWidth * scale }px`;
    iframe.style.height = `${ iframeHeight * scale }px`;
    iframe.style.transformOrigin = `left top`;
    iframe.style.transform = `scale( ${ 1 / scale } )`;

    iframe.style.marginTop = `${ offset.top }px`;
    iframe.style.marginLeft = `${ offset.left }px`;
  }
}
