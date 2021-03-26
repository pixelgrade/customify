<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Integration\Repository;

use Customify\PackageType\LocalTheme;
use Customify\Tests\Integration\TestCase;

use function Customify\plugin;

class InstalledThemesTest extends TestCase {
	protected $original_theme_directories = null;

	public function setUp(): void {
		parent::setUp();

		$this->original_theme_directories = $GLOBALS['wp_theme_directories'];
		register_theme_directory( PIXELGRADELT_RECORDS_TESTS_DIR . '/Fixture/wp-content/themes' );
		delete_site_transient( 'theme_roots' );
	}

	public function teardDown() {
		delete_site_transient( 'theme_roots' );
		$GLOBALS['wp_theme_directories'] = $this->original_theme_directories;
	}

	public function test_get_theme_from_source() {
		$repository = plugin()->get_container()['repository.local.themes'];

		// theme1 is part of the WordPress Core Unit Test package.
		$package    = $repository->first_where( [ 'slug' => 'theme1', 'source_type' => 'local.theme' ] );
		$this->assertInstanceOf( LocalTheme::class, $package );

		$package    = $repository->first_where( [ 'slug' => 'ovation', 'source_type' => 'local.theme' ] );
		$this->assertInstanceOf( LocalTheme::class, $package );

		$package    = $repository->first_where( [ 'slug' => 'ovation', 'source_type' => 'local.plugin' ] );
		$this->assertNull( $package );
	}
}
