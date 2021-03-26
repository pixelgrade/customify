<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Unit\PackageType;

use Customify\Exception\PackageNotInstalled;
use Customify\Package;
use Customify\PackageType\BasePackage;
use Customify\PackageType\LocalBasePackage;
use Customify\Tests\Unit\TestCase;

class LocalBasePackageTest extends TestCase {
	protected $package = null;

	public function setUp(): void {
		parent::setUp();

		$this->package = new class extends LocalBasePackage {
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

	public function test_directory() {
		$expected = __DIR__ . '/';
		$this->package->directory = $expected;

		$this->assertSame( $expected, $this->package->get_directory() );
	}

	public function test_is_installed() {
		$this->assertFalse( $this->package->is_installed() );

		$this->package->is_installed = true;
		$this->assertTrue( $this->package->is_installed() );
	}

	public function test_installed_version() {
		$expected = '1.0.0';
		$this->package->is_installed = true;
		$this->package->installed_version = $expected;

		$this->assertSame( $expected, $this->package->get_installed_version() );
	}

	public function test_get_installed_version_throws_exception_when_plugin_not_installed() {
		$this->expectException( PackageNotInstalled::class );

		$this->package->installed_version = '1.0.0';
		$this->package->get_installed_version();
	}
}
