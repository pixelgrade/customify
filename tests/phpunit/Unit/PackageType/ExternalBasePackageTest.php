<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Unit\PackageType;

use Composer\Semver\Constraint\MatchAllConstraint;
use Customify\Exception\PackageNotInstalled;
use Customify\Package;
use Customify\PackageType\BasePackage;
use Customify\PackageType\ExternalBasePackage;
use Customify\Tests\Unit\TestCase;

class ExternalBasePackageTest extends TestCase {
	protected $package = null;

	public function setUp(): void {
		parent::setUp();

		$this->package = new class extends ExternalBasePackage {
			public function __set( $name, $value ) {
				$this->$name = $value;
			}
		};
	}

	public function test_implements_package_interface() {
		$this->assertInstanceOf( Package::class, $this->package );
	}

	public function test_extends_base_package() {
		$this->assertInstanceOf( BasePackage::class, $this->package );
	}

	public function test_source_constraint() {
		$expected = new MatchAllConstraint();
		$this->package->source_constraint = $expected;

		$this->assertSame( $expected, $this->package->get_source_constraint() );
		$this->assertTrue( $this->package->has_source_constraint() );
	}
}
