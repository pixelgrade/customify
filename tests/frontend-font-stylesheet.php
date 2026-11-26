<?php
/**
 * Run with Customify active: wp eval-file tests/frontend-font-stylesheet.php
 * Exercises the frontend enqueue and the WordPress stylesheet printer together.
 */

$fonts = new class extends Customify_Fonts_Global {
	public function __construct() {}

	public function getFontsStylesheetUrls() {
		return array(
			'https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Libre%20Baskerville:ital,wght@0,400;1,400&display=swap',
			'https://fonts.googleapis.com/css2?family=Oswald&display=swap',
		);
	}
};

$fonts->enqueue_frontend_scripts_styles();
foreach ( $fonts->getFontsStylesheetUrls() as $key => $expected ) {
	$handle = 'customify-font-stylesheet-' . $key;
	ob_start();
	wp_styles()->do_items( array( $handle ) );
	$tag = new WP_HTML_Tag_Processor( ob_get_clean() );
	if ( ! $tag->next_tag( 'link' ) || $expected !== $tag->get_attribute( 'href' ) ) {
		WP_CLI::error( 'WordPress changed the provider URL for ' . $handle . ': ' . $tag->get_attribute( 'href' ) );
	}
}
WP_CLI::success( 'Both multi-family and single-family font URLs survived stylesheet printing.' );
