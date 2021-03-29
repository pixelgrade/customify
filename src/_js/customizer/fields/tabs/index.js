import $ from 'jquery';

export const handleTabs = () => {
  $( '.sm-tabs' ).each( function( i, obj ) {
    const $wrapper = $( obj );
    const $section = $wrapper.closest( '.control-section' );
    const $tabs = $wrapper.children( '.sm-tabs__item' );
    const targets = $tabs.map( ( i, el ) => {
      const target = $( el ).data( 'target' );
      return `sm-view-${ target }`
    } );

    const targetClassnames = targets.toArray().join( " " );

    function setActiveTab( $active ) {
      const target = $active.data( 'target' );

      $tabs.removeClass( 'sm-tabs__item--active' );
      $active.addClass( 'sm-tabs__item--active' );

      $section.removeClass( targetClassnames ).addClass( `sm-view-${ target }` );
    }

    $wrapper.on( 'click', '.sm-tabs__item', function( e ) {
      e.preventDefault();
      setActiveTab( $( this ) );
    } );

    setActiveTab( $tabs.first() );
  } );
}
