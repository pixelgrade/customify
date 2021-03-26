<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Unit\Transformer;

use Brain\Monkey\Functions;
use Composer\IO\NullIO;
use Customify\Archiver;
use Customify\Logger;
use Customify\PackageFactory;
use Customify\PackageManager;
use Customify\ReleaseManager;
use Customify\Transformer\ComposerPackageTransformer;
use Customify\Tests\Unit\TestCase;
use Psr\Log\NullLogger;

class ComposerPackageTransformerTest extends TestCase {
	protected $package = null;
	protected $transformer = null;

	public function setUp(): void {
		parent::setUp();

		$package_manager = $this->getMockBuilder( PackageManager::class )
		                        ->disableOriginalConstructor()
		                        ->getMock();

		$release_manager = $this->getMockBuilder( ReleaseManager::class )
		                        ->disableOriginalConstructor()
		                        ->getMock();

		$archiver                = new Archiver( new NullLogger() );
		$logger = new NullIO();

		$factory = new PackageFactory( $package_manager, $release_manager, $archiver, $logger );

		$this->package = $factory->create( 'plugin' )
			->set_slug( 'AcmeCode' )
			->build();

		$this->transformer = new ComposerPackageTransformer( $factory );
	}

	public function test_package_name_is_lowercased() {
		$package = $this->transformer->transform( $this->package );
		$this->assertSame( 'pixelgradelt_records/acmecode', $package->get_name() );
	}
}
