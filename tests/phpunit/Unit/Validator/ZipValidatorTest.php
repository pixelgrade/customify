<?php
declare ( strict_types = 1 );

namespace Customify\Tests\Unit\Validator;

use Customify\Exception\InvalidPackageArtifact;
use Customify\Release;
use Customify\Tests\Unit\TestCase;
use Customify\Validator\ZipValidator;

class ZipValidatorTest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->directory = PIXELGRADELT_RECORDS_TESTS_DIR . '/Fixture/wp-content/uploads/pixelgradelt-records/packages/validate';

		$this->release = $this->getMockBuilder( Release::class )
			->disableOriginalConstructor()
			->getMock();

		$this->validator = new ZipValidator();
	}

	public function test_artifact_is_valid_zip() {
		$filename = $this->directory . '/valid-zip.zip';
		$result = $this->validator->validate( $filename, $this->release );
		$this->assertTrue( $result );
	}

	public function test_validator_throws_exception_for_invalid_artifact() {
		$this->expectException( InvalidPackageArtifact::class );
		$filename = $this->directory . '/invalid-zip.zip';
		$this->validator->validate( $filename, $this->release );
	}
}
