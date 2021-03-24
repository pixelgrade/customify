<?php

namespace Customify\Vendor\Cedaro\WP\Plugin\Test;

class AbstractHookProviderTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function test_implements_interfaces()
    {
        $provider = $this->get_mock_provider();
        $this->assertInstanceOf('Customify\\Vendor\\Cedaro\\WP\\Plugin\\HookProviderInterface', $provider);
        $this->assertInstanceOf('Customify\\Vendor\\Cedaro\\WP\\Plugin\\PluginAwareInterface', $provider);
    }
    protected function get_mock_provider()
    {
        return $this->getMockBuilder('Customify\\Vendor\\Cedaro\\WP\\Plugin\\AbstractHookProvider')->getMockForAbstractClass();
    }
}
