<?php

namespace Customify\Vendor\Cedaro\WP\Plugin\Test;

use Customify\Vendor\Cedaro\WP\Plugin\Plugin;
class PluginRegisterHooksTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function test_register_hooks()
    {
        $plugin = new \Customify\Vendor\Cedaro\WP\Plugin\Plugin();
        $provider = $this->get_mock_provider();
        $class = new \ReflectionClass($provider);
        $property = $class->getProperty('plugin');
        $property->setAccessible(\true);
        $provider->expects($this->exactly(1))->method('register_hooks');
        $plugin->register_hooks($provider);
        $this->assertSame($plugin, $property->getValue($provider));
    }
    protected function get_mock_provider()
    {
        return $this->getMockBuilder('Customify\\Vendor\\Cedaro\\WP\\Plugin\\AbstractHookProvider')->getMockForAbstractClass();
    }
}
