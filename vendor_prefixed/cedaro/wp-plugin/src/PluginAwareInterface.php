<?php

/**
 * Plugin aware interface.
 *
 * @package   Cedaro\WP\Plugin
 * @copyright Copyright (c) 2015 Cedaro, LLC
 * @license   MIT
 */
namespace Customify\Vendor\Cedaro\WP\Plugin;

/**
 * Plugin aware interface.
 *
 * @package Cedaro\WP\Plugin
 */
interface PluginAwareInterface
{
    /**
     * Set the main plugin instance.
     *
     * @param  PluginInterface $plugin Main plugin instance.
     * @return $this
     */
    public function set_plugin(\Customify\Vendor\Cedaro\WP\Plugin\PluginInterface $plugin);
}
