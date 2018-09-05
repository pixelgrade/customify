( function( $, exports, wp ) {

    function swap_values( setting_one, setting_two ) {
        var color_primary = wp.customize( setting_one )();
        var color_secondary = wp.customize( setting_two )();

	    wp.customize( setting_one ).set( color_secondary );
	    wp.customize( setting_two ).set( color_primary );
    }

    wp.customize.bind( 'ready', function() {
        const $document = $( document );

        $document.on( 'click', '[data-action="sm_swap_colors"]', function( e ) {
            e.preventDefault();
            swap_values( 'sm_color_primary', 'sm_color_secondary' );
        } );

        $document.on( 'click', '[data-action="sm_swap_dark_light"]', function( e ) {
            e.preventDefault();
            swap_values( 'sm_dark_primary', 'sm_light_primary' );
            swap_values( 'sm_dark_secondary', 'sm_light_secondary' );
            swap_values( 'sm_dark_tertiary', 'sm_light_tertiary' );
        } );

        $document.on( 'click', '[data-action="sm_swap_colors_dark"]', function( e ) {
            e.preventDefault();
            swap_values( 'sm_color_primary', 'sm_dark_primary' );
            swap_values( 'sm_color_secondary', 'sm_dark_secondary' );
            swap_values( 'sm_color_tertiary', 'sm_dark_tertiary' );
        } );

        $document.on( 'click', '[data-action="sm_swap_secondary_colors_dark"]', function( e ) {
            e.preventDefault();
            swap_values( 'sm_color_secondary', 'sm_dark_secondary' );
        } );

    } );

} )( jQuery, window, wp );