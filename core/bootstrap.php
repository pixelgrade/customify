<?php defined('ABSPATH') or die;

	// ensure EXT is defined
	if ( ! defined('EXT')) {
		define('EXT', '.php');
	}

	$basepath = dirname(__FILE__).DIRECTORY_SEPARATOR;
	require $basepath.'core'.EXT;

	// load classes

	$interfacepath = $basepath.'interfaces'.DIRECTORY_SEPARATOR;
	pixcustomify::require_all($interfacepath);

	$classpath = $basepath.'classes'.DIRECTORY_SEPARATOR;
	pixcustomify::require_all($classpath);

	// load callbacks

	$callbackpath = $basepath.'callbacks'.DIRECTORY_SEPARATOR;
	pixcustomify::require_all($callbackpath);
