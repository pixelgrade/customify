<?php
declare ( strict_types = 1 );

use Customify\Tests\Framework\PHPUnitUtil;
use Customify\Tests\Framework\TestSuite;
use Psr\Log\NullLogger;

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

define( 'PIXELGRADELT_RECORDS_RUNNING_UNIT_TESTS', true );
define( 'PIXELGRADELT_RECORDS_TESTS_DIR', __DIR__ );
define( 'WP_PLUGIN_DIR', __DIR__ . '/Fixture/wp-content/plugins' );

if ( 'Unit' === PHPUnitUtil::get_current_suite() ) {
	// For the Unit suite we shouldn't need WordPress loaded.
	// This keeps them fast.
	return;
}

require_once dirname( __DIR__, 2 ) . '/vendor/antecedent/patchwork/Patchwork.php';

$suite = new TestSuite();

$GLOBALS['wp_tests_options'] = [
	'active_plugins'  => [ 'pixelgradelt_records/pixelgradelt-records.php' ],
	'timezone_string' => 'Europe/Bucharest',
];

$suite->addFilter( 'muplugins_loaded', function() {
	require dirname( __DIR__, 2 ) . '/pixelgradelt-records.php';
} );

$suite->addFilter( 'pixelgradelt_records_compose', function( $plugin, $container ) {
	$container['logger'] = new NullLogger();
	$container['storage.working_directory'] = PIXELGRADELT_RECORDS_TESTS_DIR . '/Fixture/wp-content/uploads/pixelgradelt-records/';
}, 10, 2 );

$suite->bootstrap();
