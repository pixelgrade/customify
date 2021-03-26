<?php

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Get the list of files in the provided path.
 *
 * @param string $path The relative path to the folder to get the file list for.
 *
 * @return array
 */
function customify_php_scoper_get_list_of_files( $path ) {

	$files = [];

	$directory = new RecursiveDirectoryIterator( __DIR__ . '/' . $path );
	$iterator  = new RecursiveIteratorIterator( $directory );

	while ( $iterator->valid() ) {

		if ( $iterator->isDot() || $iterator->isDir() ) {
			$iterator->next();
			continue;
		}

		$files[] = $iterator->getPathname();

		$iterator->next();
	}

	return $files;
}

$config = [
	'prefix'                     => 'Pixelgrade\Customify\Vendor',
	'whitelist-global-constants' => false,
	'whitelist-global-classes'   => false,
	'whitelist-global-functions' => false,

	/*
	By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
	directory. You can however define which files should be scoped by defining a collection of Finders in the
	following configuration key.
	For more see: https://github.com/humbug/php-scoper#finders-and-paths.
	*/
	'finders'                    => [
		Finder::create()
		      ->files()
		      ->in( 'vendor/pimple/pimple' )
		      ->exclude(
			      [
				      'Tests',
			      ]
		      )
		      ->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()
		      ->files()
		      ->in(
			      [
				      'vendor/psr/container',
				      'vendor/psr/log',
			      ]
		      )
		      ->exclude(
			      [
				      'Test',
			      ]
		      )
		      ->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()
		      ->files()
		      ->in( 'vendor/cedaro/wp-plugin' )
		      ->exclude(
			      [
				      'tests',
			      ]
		      )
		      ->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()
		      ->files()
		      ->in( 'vendor/symfony/polyfill-mbstring' )
		      ->name( [ '*.php', '*.php8', 'LICENSE', 'composer.json' ] ),
		Finder::create()
		      ->files()
		      ->in( 'vendor/symfony/polyfill-php72' )
		      ->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
	],

	/*
	When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
	original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
	support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
	heart contents.
	For more see: https://github.com/humbug/php-scoper#patchers.
	*/
	'patchers'                   => [
	],

	/*
	 * Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
	 * a file untouched.
	 * Paths are relative to the configuration file unless if they are already absolute.
	 */
	'files-whitelist'            => [
		'../vendor/symfony/polyfill-mbstring/bootstrap.php',
		'../vendor/symfony/polyfill-mbstring/Resources/mb_convert_variables.php8',
		'../vendor/symfony/polyfill-php72/bootstrap.php',
	],
];

return $config;
