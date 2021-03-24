<?php

namespace Customify\Vendor\Cedaro\WP\Plugin\Test;

use Customify\Vendor\Cedaro\WP\Plugin\Plugin;
class PluginTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function test_implements_plugin_interface()
    {
        $plugin = new \Customify\Vendor\Cedaro\WP\Plugin\Plugin();
        $this->assertInstanceOf('Customify\\Vendor\\Cedaro\\WP\\Plugin\\PluginInterface', $plugin);
    }
}
