<?php
/**
 * Regression checks for the always-iframed WordPress 7.1 block editor.
 */

define( 'ABSPATH', __DIR__ . '/' );

$registered_actions = array();

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	global $registered_actions;

	$registered_actions[] = array( $hook, $callback, $priority, $accepted_args );
}

require_once dirname( __DIR__ ) . '/includes/class-customify-block-editor.php';

$failures = array();

function assert_same( $expected, $actual, $message ) {
	global $failures;

	if ( $expected !== $actual ) {
		$failures[] = sprintf(
			'%s (expected %s, got %s)',
			$message,
			var_export( $expected, true ),
			var_export( $actual, true )
		);
	}
}

function assert_true( $actual, $message ) {
	assert_same( true, (bool) $actual, $message );
}

global $wp_version;
$wp_version = '7.1';

$reflection = new ReflectionClass( 'Customify_Block_Editor' );
$editor     = $reflection->newInstanceWithoutConstructor();

assert_same(
	'.editor-styles-wrapper',
	Customify_Block_Editor::$editor_namespace_selector,
	'The editor namespace must address the iframe canvas directly.'
);
assert_same(
	'.editor-styles-wrapper .editor-post-title',
	Customify_Block_Editor::$title_namespace_selector,
	'The title selector must use the current block-editor title class.'
);
assert_same(
	'.editor-styles-wrapper .block-editor-block-list__block',
	Customify_Block_Editor::get_block_namespace_selector(),
	'The block selector must not depend on an outer editor wrapper.'
);
assert_same(
	'.editor-styles-wrapper',
	$editor->gutenbergify_css_selectors( 'body', array( 'property' => 'background-color' ) ),
	'Body backgrounds must be scoped to the iframe canvas.'
);

$editor->add_hooks();

$dynamic_editor_hook = array_filter(
	$registered_actions,
	static function ( $registration ) use ( $editor ) {
		return 'enqueue_block_assets' === $registration[0]
			&& array( $editor, 'dynamic_styles_scripts' ) === $registration[1]
			&& 999 === $registration[2];
	}
);

assert_true(
	! empty( $dynamic_editor_hook ),
	'Dynamic editor CSS must be attached through enqueue_block_assets so WordPress loads it in the iframe.'
);
assert_true(
	method_exists( $editor, 'is_admin_block_editor_screen' ),
	'The iframe-safe asset hook must be guarded to admin block-editor screens.'
);

if ( ! empty( $failures ) ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

fwrite( STDOUT, "WordPress 7.1 editor iframe contract passed.\n" );
