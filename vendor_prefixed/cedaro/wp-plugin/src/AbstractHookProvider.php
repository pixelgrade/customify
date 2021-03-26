<?php

/**
 * Base hook provider.
 *
 * @package   Cedaro\WP\Plugin
 * @copyright Copyright (c) 2017 Cedaro, LLC
 * @license   MIT
 */
namespace Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin;

/**
 * Base hook provider class.
 *
 * @package Cedaro\WP\Plugin
 */
abstract class AbstractHookProvider implements \Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\HookProviderInterface, \Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\PluginAwareInterface
{
    use HooksTrait, PluginAwareTrait;
    /**
     * Registers hooks for the plugin.
     */
    public abstract function register_hooks();
}
