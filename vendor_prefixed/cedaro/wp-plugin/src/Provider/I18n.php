<?php

/**
 * Internationalization provider.
 *
 * @package   Cedaro\WP\Plugin
 * @copyright Copyright (c) 2015 Cedaro, LLC
 * @license   MIT
 */
namespace Customify\Vendor\Cedaro\WP\Plugin\Provider;

use Customify\Vendor\Cedaro\WP\Plugin\HookProviderInterface;
use Customify\Vendor\Cedaro\WP\Plugin\HooksTrait;
use Customify\Vendor\Cedaro\WP\Plugin\PluginAwareInterface;
use Customify\Vendor\Cedaro\WP\Plugin\PluginAwareTrait;
/**
 * Internationalization class.
 *
 * @package Cedaro\WP\Plugin
 */
class I18n implements \Customify\Vendor\Cedaro\WP\Plugin\PluginAwareInterface, \Customify\Vendor\Cedaro\WP\Plugin\HookProviderInterface
{
    use HooksTrait, PluginAwareTrait;
    /**
     * Register hooks.
     *
     * Loads the text domain during the `plugins_loaded` action.
     */
    public function register_hooks()
    {
        if (did_action('plugins_loaded')) {
            $this->load_textdomain();
        } else {
            $this->add_action('plugins_loaded', 'load_textdomain');
        }
    }
    /**
     * Load the text domain to localize the plugin.
     */
    protected function load_textdomain()
    {
        $plugin_rel_path = \dirname($this->plugin->get_basename()) . '/languages';
        load_plugin_textdomain($this->plugin->get_slug(), \false, $plugin_rel_path);
    }
}
