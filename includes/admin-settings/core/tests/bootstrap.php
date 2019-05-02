<?php defined('ABSPATH') or die;

	// ensure EXT is defined
	if ( ! defined('EXT')) {
		define('EXT', '.php');
	}

	error_reporting(-1);

	$basepath = realpath('..').DIRECTORY_SEPARATOR;
	require $basepath.'bootstrap'.EXT;
