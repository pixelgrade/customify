<?php

/**
 * Basic implementation of PluginAwareInterface.
 *
 * @package   Cedaro\WP\Plugin
 * @copyright Copyright (c) 2015 Cedaro, LLC
 * @license   MIT
 */
namespace Customify\Vendor\Cedaro\WP\Plugin;

/**
 * Plugin aware trait.
 *
 * @package Cedaro\WP\Plugin
 */
trait PluginAwareTrait
{
    /**
     * Main plugin instance.
     *
     * @var PluginInterface
     */
    protected $plugin;
    /**
     * Set the main plugin instance.
     *
     * @param  PluginInterface $plugin Main plugin instance.
     * @return $this
     */
    public function set_plugin(\Customify\Vendor\Cedaro\WP\Plugin\PluginInterface $plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }
}