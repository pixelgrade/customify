const defaultOffset = {
  top: 0,
  right: 0,
  bottom: 0,
  left: 0,
};

let offset = defaultOffset;

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
  const preview = document.querySelector( '.wp-full-overlay' );
  const iframe = document.querySelector( '#customize-preview iframe' );
  const previewedDevice = wp.customize.previewedDevice.get();

  if ( ! iframe || ! preview ) {
    return;
  }

  // remove CSS properties that may have been previously added
  iframe.style.removeProperty( 'width' );
  iframe.style.removeProperty( 'height' );
  iframe.style.removeProperty( 'transform-origin' );
  iframe.style.removeProperty( 'transform' );
  iframe.style.removeProperty( 'margin-top' );
  iframe.style.removeProperty( 'margin-left' );

  if ( ! iframe ) {
    return;
  }

  // scaling of the site preview should be done only in desktop preview mode
  if ( previewedDevice !== 'desktop' ) {
    return;
  }

  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;

  const previewWidth = preview.offsetWidth;
  const previewHeight = preview.offsetHeight;

  // for an accurate preview at resolutions where media queries may intervene
  // increase the width of the preview and use CSS transforms to scale it back down
  const shouldScale = previewWidth > 720 && previewWidth < 1100;

  const initialHeight = previewHeight;
  const finalHeight = previewHeight - offset.top - offset.bottom;

  const initialWidth = shouldScale ? windowWidth : previewWidth;
  const finalWidth = previewWidth - offset.left - offset.right;

  const scaleX = initialWidth / finalWidth;
  const scaleY = initialHeight / finalHeight;
  const scale = Math.max( scaleX, scaleY );

  iframe.style.width = `${ finalWidth * scale }px`;
  iframe.style.height = `${ finalHeight * scale }px`;
  iframe.style.transformOrigin = `left top`;
  iframe.style.transform = `scale( ${ 1 / scale } )`;

  iframe.style.marginTop = `${ offset.top }px`;
  iframe.style.marginLeft = `${ offset.left }px`;
}
