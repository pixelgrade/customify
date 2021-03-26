<?php
/**
 * Test suite.
 *
 * @package   Customify\Tests
 * @copyright Copyright (c) 2019 Cedaro, LLC
 * @license   MIT
 */

declare ( strict_types=1 );

namespace Customify\Tests\Framework;

use Exception;
use PHPUnit\Framework\TestSuite as PHPUnitTestSuite;

/**
 * Test suite class.
 *
 * @package Customify\Tests
 */
class TestSuite extends PHPUnitTestSuite {
	/**
	 * Path to the WordPress tests.
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Create a test suite.
	 *
	 * @param string $tests_directory Optional. Path to the WordPress tests.
	 */
	public function __construct( $tests_directory = '' ) {
		if ( empty( $tests_directory ) ) {
			$tests_directory = $this->findSuite();
		}

		$this->directory = rtrim( $tests_directory, '/' );
	}

	/**
	 * Bootstrap the WordPress test suite.
	 */
	public function bootstrap() {
		if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
			define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
		}

		require $this->directory . '/includes/bootstrap.php';
	}

	/**
	 * Add hooks before loading WP.
	 *
	 * @param string       $tag             The name for the filter to add.
	 * @param object|array $function_to_add The function/callback to execute on call.
	 * @param int          $priority        The priority.
	 * @param int          $accepted_args   The amount of accepted arguments.
	 *
	 * @return bool Always true.
	 */
	public static function addFilter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		global $wp_filter;

		if ( function_exists( 'add_filter' ) ) {
			add_filter( $tag, $function_to_add, $priority, $accepted_args );
		} else {
			$idx                                    = self::getUniqueFilterId( $tag, $function_to_add, $priority );
			$wp_filter[ $tag ][ $priority ][ $idx ] = array(
				'function'      => $function_to_add,
				'accepted_args' => $accepted_args,
			);
		}

		return true;
	}

	/**
	 * Generate a unique function ID based on the given arguments.
	 *
	 * @param string       $tag      Unused. The name of the filter to build ID for.
	 * @param object|array $function The function to generate ID for.
	 * @param int          $priority Unused. The priority.
	 *
	 * @return string Unique function ID.
	 */
	protected static function getUniqueFilterId( $tag, $function, $priority ) {
		if ( is_string( $function ) ) {
			return $function;
		}

		if ( is_object( $function ) ) {
			// Closures are currently implemented as objects.
			$function = array( $function, '' );
		} else {
			$function = (array) $function;
		}

		if ( is_object( $function[0] ) ) {
			return spl_object_hash( $function[0] ) . $function[1];
		} elseif ( is_string( $function[0] ) ) {
			// Static Calling.
			return $function[0] . $function[1];
		}
	}

	/**
	 * Locate the WordPress test suite.
	 *
	 * - WP_TESTS_DIR environment variable
	 * - Adjacent WordPress dependency in the vendor directory
	 * - WP_DEVELOP_DIR environment variable
	 * - Location WP CLI installs the tests
	 *
	 * @link https://core.trac.wordpress.org/browser/trunk?order=name
	 * @link https://github.com/wp-cli/scaffold-command/blob/master/templates/install-wp-tests.sh
	 *
	 * @throws Exception When the WordPress test suite can't be found.
	 * @return string Absolute path to the tests.
	 */
	protected function findSuite(): string {
		$directories   = isset( $_ENV['WP_TESTS_DIR'] ) ? [ $_ENV['WP_TESTS_DIR'] ] : [];
		$directories[] = realpath( 'vendor/wordpress/wordpress/tests/phpunit' );

		if ( isset( $_ENV['WP_DEVELOP_DIR'] ) ) {
			$directories[] = $_ENV['WP_DEVELOP_DIR'] . 'tests/phpunit';
		}

		$directories[] = '/tmp/wordpress-tests-lib';

		foreach ( array_filter( $directories ) as $directory ) {
			if ( file_exists( $directory ) ) {
				return $directory;
			}
		}

		throw new Exception( 'WordPress test suite not found.' );
	}
}
