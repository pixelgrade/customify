<?php
declare ( strict_types=1 );

namespace Customify\Tests\Unit\PackageType\Builder;

use Composer\IO\NullIO;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Semver\Constraint\MultiConstraint;
use Customify\Archiver;
use Customify\Package;
use Customify\PackageManager;
use Customify\PackageType\Builder\ExternalBasePackageBuilder;
use Customify\PackageType\Builder\BasePackageBuilder;
use Customify\PackageType\ExternalBasePackage;
use Customify\ReleaseManager;
use Customify\Tests\Framework\PHPUnitUtil;
use Customify\Tests\Unit\TestCase;
use Psr\Log\NullLogger;

class ExternalPackageBuilderTest extends TestCase {
	protected $builder = null;

	public function setUp(): void {
		parent::setUp();

		// Provide direct getters.
		$package = new class extends ExternalBasePackage {
			public function __get( $name ) {
				return $this->$name;
			}
		};


		$package_manager = $this->getMockBuilder( PackageManager::class )
		                        ->disableOriginalConstructor()
		                        ->getMock();

		$release_manager = $this->getMockBuilder( ReleaseManager::class )
		                        ->disableOriginalConstructor()
		                        ->getMock();

		$archiver = new Archiver( new NullLogger() );
		$logger = new NullIO();

		$this->builder = new ExternalBasePackageBuilder( $package, $package_manager, $release_manager, $archiver, $logger );
	}

	public function test_extends_package_builder() {

		$this->assertInstanceOf( BasePackageBuilder::class, $this->builder );
	}

	public function test_implements_package_interface() {
		$package = $this->builder->build();

		$this->assertInstanceOf( Package::class, $package );
	}

	public function test_source_constraint() {
		$expected = $this->getMockBuilder( MultiConstraint::class )
		                 ->disableOriginalConstructor()
		                 ->getMock();

		$package = $this->builder->set_source_constraint( $expected )->build();

		$this->assertSame( $expected, $package->source_constraint );
	}

	public function test_with_package() {
		$expected = new class extends ExternalBasePackage {
			public function __get( $name ) {
				return $this->$name;
			}
			public function __set( $name, $value ) {
				$this->$name = $value;
			}
		};
		$expected->source_constraint = new MatchAllConstraint();

		$package = $this->builder->with_package( $expected )->build();

		$this->assertSame( $expected->source_constraint, $package->source_constraint );
	}
}
