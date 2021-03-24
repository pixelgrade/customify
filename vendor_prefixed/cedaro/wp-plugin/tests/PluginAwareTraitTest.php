<?php

namespace Customify\Vendor\Cedaro\WP\Plugin\Test;

use Customify\Vendor\Cedaro\WP\Plugin\Plugin;
use Customify\Vendor\Cedaro\WP\Plugin\PluginAwareTrait;
class PluginAwareTraitTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function test_set_plugin()
    {
        $provider = $this->getMockForTrait('Customify\\Vendor\\Cedaro\\WP\\Plugin\\PluginAwareTrait');
        $class = new \ReflectionClass($provider);
        $property = $class->getProperty('plugin');
        $property->setAccessible(\true);
        $plugin = new \Customify\Vendor\Cedaro\WP\Plugin\Plugin();
        $provider->set_plugin($plugin);
        $this->assertSame($plugin, $property->getValue($provider));
    }
}
