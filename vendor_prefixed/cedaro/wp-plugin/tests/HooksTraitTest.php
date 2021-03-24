<?php

namespace Customify\Vendor\Cedaro\WP\Plugin\Test;

use Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase;
use Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\Mock\HookProvider;
class HooksTraitTest extends \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase
{
    public function test_filters_added()
    {
        $provider = $this->get_mock_provider();
        $provider->expects($this->exactly(1))->method('add_filter')->will($this->returnCallback(function ($hook, $method, $priority, $arg_count) {
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame('the_title', $hook);
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame(10, $priority);
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame(1, $arg_count);
        }));
        $provider->register_filters();
    }
    public function test_actions_added()
    {
        $provider = $this->get_mock_provider();
        $provider->expects($this->exactly(1))->method('add_filter')->will($this->returnCallback(function ($hook, $method, $priority, $arg_count) {
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame('template_redirect', $hook);
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame(10, $priority);
            \Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\TestCase::assertSame(1, $arg_count);
        }));
        $provider->register_actions();
    }
    protected function get_mock_provider()
    {
        return $this->getMockBuilder(\Customify\Vendor\Cedaro\WP\Plugin\Test\Framework\Mock\HookProvider::class)->setMethods(['add_filter'])->getMock();
    }
}
