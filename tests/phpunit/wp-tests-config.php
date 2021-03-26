<?php
/**
 * Configuration for the WordPress testing suite.
 *
 * @package   Customify\Tests
 * @copyright Copyright (c) 2019 Cedaro, LLC
 * @license   MIT
 */

/**
 * LOAD OUR TEST ENVIRONMENT VARIABLES FROM .ENV
 */
require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
// We use immutable since we don't want to overwrite variables already set.
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['WP_TESTS_DB_NAME', 'WP_TESTS_DB_USER', 'WP_TESTS_DB_PASSWORD', 'WP_TESTS_DB_HOST']);

/**
 * PROCEED
 */

// Path to the WordPress codebase to test. Add a forward slash in the end.
define( 'ABSPATH', realpath( dirname( __DIR__, 2 ) . '/vendor/wordpress/wordpress/src' ) . '/' );

// Path to the theme to test with.
define( 'WP_DEFAULT_THEME', 'default' );

// Test with WordPress debug mode (default).
define( 'WP_DEBUG', true );

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

define( 'DB_NAME', isset( $_ENV['WP_TESTS_DB_NAME'] ) ? $_ENV['WP_TESTS_DB_NAME'] : 'wordpress_test' );
define( 'DB_USER', isset( $_ENV['WP_TESTS_DB_USER'] ) ? $_ENV['WP_TESTS_DB_USER'] : 'root' );
define( 'DB_PASSWORD', isset( $_ENV['WP_TESTS_DB_PASSWORD'] ) ? $_ENV['WP_TESTS_DB_PASSWORD'] : '' );
define( 'DB_HOST', isset( $_ENV['WP_TESTS_DB_HOST'] ) ? $_ENV['WP_TESTS_DB_HOST'] : 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';   // Only numbers, letters, and underscores!

// Test suite configuration.
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );
