<?php

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Customify\Vendor\Pimple\Tests;

use Customify\Vendor\PHPUnit\Framework\TestCase;
use Customify\Vendor\Pimple\Container;
/**
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class PimpleTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function testWithString()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['param'] = 'value';
        $this->assertEquals('value', $pimple['param']);
    }
    public function testWithClosure()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $pimple['service']);
    }
    public function testServicesShouldBeDifferent()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = $pimple->factory(function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        });
        $serviceOne = $pimple['service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceOne);
        $serviceTwo = $pimple['service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
    }
    public function testShouldPassContainerAsParameter()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $pimple['container'] = function ($container) {
            return $container;
        };
        $this->assertNotSame($pimple, $pimple['service']);
        $this->assertSame($pimple, $pimple['container']);
    }
    public function testIsset()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['param'] = 'value';
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $pimple['null'] = null;
        $this->assertTrue(isset($pimple['param']));
        $this->assertTrue(isset($pimple['service']));
        $this->assertTrue(isset($pimple['null']));
        $this->assertFalse(isset($pimple['non_existent']));
    }
    public function testConstructorInjection()
    {
        $params = ['param' => 'value'];
        $pimple = new \Customify\Vendor\Pimple\Container($params);
        $this->assertSame($params['param'], $pimple['param']);
    }
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        echo $pimple['foo'];
    }
    /**
     * @group legacy
     */
    public function testLegacyOffsetGetValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        echo $pimple['foo'];
    }
    public function testOffsetGetHonorsNullValues()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = null;
        $this->assertNull($pimple['foo']);
    }
    public function testUnset()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['param'] = 'value';
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        unset($pimple['param'], $pimple['service']);
        $this->assertFalse(isset($pimple['param']));
        $this->assertFalse(isset($pimple['service']));
    }
    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testShare($service)
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['shared_service'] = $service;
        $serviceOne = $pimple['shared_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceOne);
        $serviceTwo = $pimple['shared_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
    }
    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testProtect($service)
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['protected'] = $pimple->protect($service);
        $this->assertSame($service, $pimple['protected']);
    }
    public function testGlobalFunctionNameAsParameterValue()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['global_function'] = 'strlen';
        $this->assertSame('strlen', $pimple['global_function']);
    }
    public function testRaw()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = $definition = $pimple->factory(function () {
            return 'foo';
        });
        $this->assertSame($definition, $pimple->raw('service'));
    }
    public function testRawHonorsNullValues()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = null;
        $this->assertNull($pimple->raw('foo'));
    }
    public function testFluentRegister()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $this->assertSame($pimple, $pimple->register($this->getMockBuilder('Customify\\Vendor\\Pimple\\ServiceProviderInterface')->getMock()));
    }
    public function testRawValidatesKeyIsPresent()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->raw('foo');
    }
    /**
     * @group legacy
     */
    public function testLegacyRawValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->raw('foo');
    }
    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testExtend($service)
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['shared_service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $pimple['factory_service'] = $pimple->factory(function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        });
        $pimple->extend('shared_service', $service);
        $serviceOne = $pimple['shared_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceOne);
        $serviceTwo = $pimple['shared_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
        $this->assertSame($serviceOne->value, $serviceTwo->value);
        $pimple->extend('factory_service', $service);
        $serviceOne = $pimple['factory_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceOne);
        $serviceTwo = $pimple['factory_service'];
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
        $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    }
    public function testExtendDoesNotLeakWithFactories()
    {
        if (\extension_loaded('pimple')) {
            $this->markTestSkipped('Pimple extension does not support this test');
        }
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = $pimple->factory(function () {
            return;
        });
        $pimple['foo'] = $pimple->extend('foo', function ($foo, $pimple) {
            return;
        });
        unset($pimple['foo']);
        $p = new \ReflectionProperty($pimple, 'values');
        $p->setAccessible(\true);
        $this->assertEmpty($p->getValue($pimple));
        $p = new \ReflectionProperty($pimple, 'factories');
        $p->setAccessible(\true);
        $this->assertCount(0, $p->getValue($pimple));
    }
    public function testExtendValidatesKeyIsPresent()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->extend('foo', function () {
        });
    }
    /**
     * @group legacy
     */
    public function testLegacyExtendValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->extend('foo', function () {
        });
    }
    public function testKeys()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = 123;
        $pimple['bar'] = 123;
        $this->assertEquals(['foo', 'bar'], $pimple->keys());
    }
    /** @test */
    public function settingAnInvokableObjectShouldTreatItAsFactory()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['invokable'] = new \Customify\Vendor\Pimple\Tests\Fixtures\Invokable();
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\Service', $pimple['invokable']);
    }
    /** @test */
    public function settingNonInvokableObjectShouldTreatItAsParameter()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['non_invokable'] = new \Customify\Vendor\Pimple\Tests\Fixtures\NonInvokable();
        $this->assertInstanceOf('Customify\\Vendor\\Pimple\\Tests\\Fixtures\\NonInvokable', $pimple['non_invokable']);
    }
    /**
     * @dataProvider badServiceDefinitionProvider
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\ExpectedInvokableException::class);
        $this->expectExceptionMessage('Service definition is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->factory($service);
    }
    /**
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyFactoryFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service definition is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->factory($service);
    }
    /**
     * @dataProvider badServiceDefinitionProvider
     */
    public function testProtectFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\ExpectedInvokableException::class);
        $this->expectExceptionMessage('Callable is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->protect($service);
    }
    /**
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyProtectFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Callable is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple->protect($service);
    }
    /**
     * @dataProvider badServiceDefinitionProvider
     */
    public function testExtendFailsForKeysNotContainingServiceDefinitions($service)
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\InvalidServiceIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" does not contain an object definition.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = $service;
        $pimple->extend('foo', function () {
        });
    }
    /**
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyExtendFailsForKeysNotContainingServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" does not contain an object definition.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = $service;
        $pimple->extend('foo', function () {
        });
    }
    /**
     * @group legacy
     * @expectedDeprecation How Pimple behaves when extending protected closures will be fixed in Pimple 4. Are you sure "foo" should be protected?
     */
    public function testExtendingProtectedClosureDeprecation()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = $pimple->protect(function () {
            return 'bar';
        });
        $pimple->extend('foo', function ($value) {
            return $value . '-baz';
        });
        $this->assertSame('bar-baz', $pimple['foo']);
    }
    /**
     * @dataProvider badServiceDefinitionProvider
     */
    public function testExtendFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\ExpectedInvokableException::class);
        $this->expectExceptionMessage('Extension service definition is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
        };
        $pimple->extend('foo', $service);
    }
    /**
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyExtendFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension service definition is not a Closure or invokable object.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
        };
        $pimple->extend('foo', $service);
    }
    public function testExtendFailsIfFrozenServiceIsNonInvokable()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\FrozenServiceException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\NonInvokable();
        };
        $foo = $pimple['foo'];
        $pimple->extend('foo', function () {
        });
    }
    public function testExtendFailsIfFrozenServiceIsInvokable()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\FrozenServiceException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Invokable();
        };
        $foo = $pimple['foo'];
        $pimple->extend('foo', function () {
        });
    }
    /**
     * Provider for invalid service definitions.
     */
    public function badServiceDefinitionProvider()
    {
        return [[123], [new \Customify\Vendor\Pimple\Tests\Fixtures\NonInvokable()]];
    }
    /**
     * Provider for service definitions.
     */
    public function serviceDefinitionProvider()
    {
        return [[function ($value) {
            $service = new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
            $service->value = $value;
            return $service;
        }], [new \Customify\Vendor\Pimple\Tests\Fixtures\Invokable()]];
    }
    public function testDefiningNewServiceAfterFreeze()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $foo = $pimple['foo'];
        $pimple['bar'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $pimple['bar']);
    }
    public function testOverridingServiceAfterFreeze()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\FrozenServiceException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $foo = $pimple['foo'];
        $pimple['foo'] = function () {
            return 'bar';
        };
    }
    /**
     * @group legacy
     */
    public function testLegacyOverridingServiceAfterFreeze()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $foo = $pimple['foo'];
        $pimple['foo'] = function () {
            return 'bar';
        };
    }
    public function testRemovingServiceAfterFreeze()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $foo = $pimple['foo'];
        unset($pimple['foo']);
        $pimple['foo'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $pimple['foo']);
    }
    public function testExtendingService()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $pimple['foo'] = $pimple->extend('foo', function ($foo, $app) {
            return "{$foo}.bar";
        });
        $pimple['foo'] = $pimple->extend('foo', function ($foo, $app) {
            return "{$foo}.baz";
        });
        $this->assertSame('foo.bar.baz', $pimple['foo']);
    }
    public function testExtendingServiceAfterOtherServiceFreeze()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['foo'] = function () {
            return 'foo';
        };
        $pimple['bar'] = function () {
            return 'bar';
        };
        $foo = $pimple['foo'];
        $pimple['bar'] = $pimple->extend('bar', function ($bar, $app) {
            return "{$bar}.baz";
        });
        $this->assertSame('bar.baz', $pimple['bar']);
    }
}
