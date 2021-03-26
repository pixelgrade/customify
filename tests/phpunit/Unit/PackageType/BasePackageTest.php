<?php
declare ( strict_types=1 );

namespace Customify\Tests\Unit\PackageType;

use Customify\Package;
use Customify\PackageType\BasePackage;
use Customify\Tests\Unit\TestCase;

class BasePackageTest extends TestCase {
	protected $package = null;

	public function setUp(): void {
		parent::setUp();

		$this->package = new class extends BasePackage {
			public function __set( $name, $value ) {
				$this->$name = $value;
			}
		};
	}

	public function test_implements_package_interface() {
		$this->assertInstanceOf( Package::class, $this->package );
	}

	public function test_name() {
		$expected            = 'PixelgradeLT Records';
		$this->package->name = $expected;

		$this->assertSame( $expected, $this->package->get_name() );
	}

	public function test_type() {
		$expected            = 'plugin';
		$this->package->type = $expected;

		$this->assertSame( $expected, $this->package->get_type() );
	}

	public function test_source_type() {
		$expected                   = 'local.plugin';
		$this->package->source_type = $expected;

		$this->assertSame( $expected, $this->package->get_source_type() );
	}

	public function test_source_name() {
		$expected                   = 'local/plugin';
		$this->package->source_name = $expected;

		$this->assertSame( $expected, $this->package->get_source_name() );
	}

	public function test_slug() {
		$expected            = 'pixelgradelt_records';
		$this->package->slug = $expected;

		$this->assertSame( $expected, $this->package->get_slug() );
	}

	public function test_authors() {
		$expected               = [
			[
				'name'     => 'Pixelgrade',
				'email'    => 'contact@pixelgrade.com',
				'homepage' => 'https://pixelgrade.com',
				'role'     => 'Maker',
			],
		];
		$this->package->authors = $expected;

		$this->assertSame( $expected, $this->package->get_authors() );
	}

	public function test_description() {
		$expected                   = 'A package description.';
		$this->package->description = $expected;

		$this->assertSame( $expected, $this->package->get_description() );
	}

	public function test_homepage() {
		$expected                = 'https://www.cedaro.com/';
		$this->package->homepage = $expected;

		$this->assertSame( $expected, $this->package->get_homepage() );
	}

	public function test_license() {
		$expected               = 'GPL-2.0-only';
		$this->package->license = $expected;

		$this->assertSame( $expected, $this->package->get_license() );
	}

	public function test_keywords() {
		$expected                = [ 'key0', 'key1', 'key2', 'key3', ];
		$this->package->keywords = $expected;

		$this->assertSame( $expected, $this->package->get_keywords() );
	}

	public function test_requires_at_least_wp() {
		$expected                            = '5.6.2';
		$this->package->requires_at_least_wp = $expected;

		$this->assertSame( $expected, $this->package->get_requires_at_least_wp() );
	}

	public function test_tested_up_to_wp() {
		$expected                       = '5.6.2';
		$this->package->tested_up_to_wp = $expected;

		$this->assertSame( $expected, $this->package->get_tested_up_to_wp() );
	}

	public function test_requires_php() {
		$expected                    = '8.0.1';
		$this->package->requires_php = $expected;

		$this->assertSame( $expected, $this->package->get_requires_php() );
	}

	public function test_is_managed() {
		$expected                  = true;
		$this->package->is_managed = $expected;

		$this->assertSame( $expected, $this->package->is_managed() );
		$this->assertSame( $expected, $this->package->get_is_managed() );
	}

	public function test_managed_post_id() {
		$expected                       = 123;
		$this->package->managed_post_id = $expected;

		$this->assertSame( $expected, $this->package->get_managed_post_id() );
	}

	public function test_required_packages() {
		$expected                         = [
			'some_pseudo_id' => [
				'composer_package_name' => 'pixelgrade/test',
				'version_range'         => '*',
				'stability'             => 'stable',
				'source_name'           => 'local-plugin/test',
				'managed_post_id'       => 123,
			],
		];
		$this->package->required_packages = $expected;

		$this->assertSame( $expected, $this->package->get_required_packages() );
		$this->assertTrue( $this->package->has_required_packages() );
	}

	public function test_has_source_constraint() {

		$this->assertFalse( $this->package->has_source_constraint() );
	}
}
