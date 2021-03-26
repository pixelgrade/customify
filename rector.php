<?php

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\DowngradeSetList;
use RectorPrefix20210326\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/** Define ABSPATH as this file's directory */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/vendor/wordpress/wordpress/');
}

return static function (ContainerConfigurator $containerConfigurator): void {
	// get parameters
	$parameters = $containerConfigurator->parameters();

	// paths to refactor; solid alternative to CLI arguments
	$parameters->set(Option::PATHS, [
		__DIR__ . '/src',
		__DIR__ . '/vendor_prefixed',
	]);

	// Rector is static reflection to load code without running it - see https://phpstan.org/blog/zero-config-analysis-with-static-reflection
	$parameters->set(Option::AUTOLOAD_PATHS, [
		__DIR__ . "/vendor/autoload.php",
		__DIR__ . "/src/functions.php",
		__DIR__ . "/vendor_prefixed/symfony/polyfill-mbstring/bootstrap.php",
		__DIR__ . "/vendor_prefixed/symfony/polyfill-php72/bootstrap.php",
		__DIR__ . "/vendor_prefixed",
		__DIR__ . '/vendor/wordpress/wordpress',
	]);

	// do you need to include constants, class aliases or custom autoloader? files listed will be executed
	$parameters->set(Option::BOOTSTRAP_FILES, [

	]);

	// here we can define, what sets of rules will be applied
	$parameters->set(Option::SETS, [
		DowngradeSetList::PHP_80,
		DowngradeSetList::PHP_74,
		DowngradeSetList::PHP_73,
		DowngradeSetList::PHP_72,
		DowngradeSetList::PHP_71,
		DowngradeSetList::PHP_70,
	]);

	// is your PHP version different from the one your refactor to? [default: your PHP version]
	$parameters->set(Option::PHP_VERSION_FEATURES, '7.4');
};
