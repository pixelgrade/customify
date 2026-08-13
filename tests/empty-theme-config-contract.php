<?php
/**
 * Regression checks for themes that do not provide a legacy Customify config.
 */

define( 'ABSPATH', __DIR__ . '/' );

function current_theme_supports( $feature ) {
	return false;
}

function apply_filters( $hook, $value ) {
	if ( in_array( $hook, array( 'customify_style_manager_is_supported', 'style_manager_font_palettes_are_supported' ), true ) ) {
		return true;
	}

	return $value;
}

function esc_html__( $text ) {
	return $text;
}

function __( $text ) {
	return $text;
}

function wp_kses_post( $text ) {
	return $text;
}

function PixCustomifyPlugin() {
	return new class() {
		public function get_options_configs( $force = false ) {
			return array();
		}
	};
}

require_once dirname( __DIR__ ) . '/includes/class-customify-font-palettes.php';
require_once dirname( __DIR__ ) . '/includes/class-customify-style-manager.php';

$warnings = array();
set_error_handler(
	static function ( $severity, $message ) use ( &$warnings ) {
		$warnings[] = $message;
		return true;
	}
);

$font_palettes = ( new ReflectionClass( 'Customify_Font_Palettes' ) )->newInstanceWithoutConstructor();
$style_manager = ( new ReflectionClass( 'Customify_Style_Manager' ) )->newInstanceWithoutConstructor();

$font_config = $font_palettes->standardize_connected_fields( array() );
$style_config = $style_manager->reorganize_customify_sections(
	array(
		'panels' => array(
			'style_manager_panel' => array(),
		),
	)
);

restore_error_handler();

if ( ! empty( $warnings ) ) {
	fwrite( STDERR, implode( PHP_EOL, $warnings ) . PHP_EOL );
	exit( 1 );
}

if ( array() !== $font_config ) {
	fwrite( STDERR, "An empty font configuration should remain unchanged.\n" );
	exit( 1 );
}

if ( ! isset( $style_config['panels']['style_manager_panel'] ) ) {
	fwrite( STDERR, "The Style Manager panel should remain available.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Empty theme configuration contract passed.\n" );
