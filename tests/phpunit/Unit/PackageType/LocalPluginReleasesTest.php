<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Unit\PackageType;

use Brain\Monkey\Functions;
use Composer\IO\NullIO;
use Composer\Semver\VersionParser;
use Customify\Client\ComposerClient;
use Customify\ComposerVersionParser;
use Customify\PackageManager;
use Psr\Log\NullLogger;
use Customify\Archiver;
use Customify\PackageType\LocalPlugin;
use Customify\PackageType\Builder\LocalPluginBuilder;
use Customify\Release;
use Customify\ReleaseManager;
use Customify\Storage\Local as LocalStorage;
use Customify\Tests\Unit\TestCase;

class LocalPluginReleasesTest extends TestCase {
	protected $builder = null;

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'get_site_transient' )->justReturn( $this->get_update_transient() );

		$archiver = new Archiver( new NullLogger() );
		$storage  = new LocalStorage( PIXELGRADELT_RECORDS_TESTS_DIR . '/Fixture/wp-content/uploads/pixelgradelt-records/packages' );
		$composer_version_parser = new ComposerVersionParser( new VersionParser() );
		$composer_client = new ComposerClient();

		$package_manager = $this->getMockBuilder( PackageManager::class )
		                        ->disableOriginalConstructor()
		                        ->getMock();

		$release_manager = new ReleaseManager( $storage, $archiver, $composer_version_parser, $composer_client );

		$logger = new NullIO();

		$package  = new LocalPlugin();

		$this->builder = ( new LocalPluginBuilder( $package, $package_manager, $release_manager, $archiver, $logger ) )
			->set_source_name( 'local-plugin' . '/' . 'basic' )
			->set_source_type( 'local.plugin' )
			->set_basename( 'basic/basic.php' )
			->set_slug( 'basic' )
			->set_type( 'plugin' );
	}

	public function test_get_cached_releases_from_storage() {
		$package = $this->builder
			->add_cached_releases()
			->build();

		$this->assertInstanceOf( Release::class, $package->get_release( '1.0.0' ) );
	}

	public function test_get_cached_releases_includes_installed_version() {
		$package = $this->builder
			->set_installed( true )
			->set_installed_version( '1.3.1' )
			->add_cached_releases()
			->build();

		$this->assertSame( '1.3.1', $package->get_installed_release()->get_version() );
	}

	public function test_get_cached_releases_includes_pending_update() {
		$package = $this->builder
			->set_installed( true )
			->set_installed_version( '1.3.1' )
			->add_cached_releases()
			->build();

		$this->assertSame( '2.0.0', $package->get_latest_release()->get_version() );
	}

	protected function get_update_transient() {
		return (object) [
			'response' => [
				'basic/basic.php' => (object) [
					'slug'        => 'basic',
					'plugin'      => 'basic/basic.php',
					'new_version' => '2.0.0',
					'package'     => 'https://example.org/download/basic/2.0.0.zip',
				],
			],
		];
	}
}
