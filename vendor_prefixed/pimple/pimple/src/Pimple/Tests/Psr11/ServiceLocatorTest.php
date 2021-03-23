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
namespace Customify\Vendor\Pimple\Tests\Psr11;

use Customify\Vendor\PHPUnit\Framework\TestCase;
use Customify\Vendor\Pimple\Container;
use Customify\Vendor\Pimple\Psr11\ServiceLocator;
use Customify\Vendor\Pimple\Tests\Fixtures;
/**
 * ServiceLocator test case.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class ServiceLocatorTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function testCanAccessServices()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['service']);
        $this->assertSame($pimple['service'], $locator->get('service'));
    }
    public function testCanAccessAliasedServices()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['alias' => 'service']);
        $this->assertSame($pimple['service'], $locator->get('alias'));
    }
    public function testCannotAccessAliasedServiceUsingRealIdentifier()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "service" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['alias' => 'service']);
        $service = $locator->get('service');
    }
    public function testGetValidatesServiceCanBeLocated()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['alias' => 'service']);
        $service = $locator->get('foo');
    }
    public function testGetValidatesTargetServiceExists()
    {
        $this->expectException(\Customify\Vendor\Pimple\Exception\UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "invalid" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['alias' => 'invalid']);
        $service = $locator->get('alias');
    }
    public function testHasValidatesServiceCanBeLocated()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service1'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $pimple['service2'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['service1']);
        $this->assertTrue($locator->has('service1'));
        $this->assertFalse($locator->has('service2'));
    }
    public function testHasChecksIfTargetServiceExists()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $locator = new \Customify\Vendor\Pimple\Psr11\ServiceLocator($pimple, ['foo' => 'service', 'bar' => 'invalid']);
        $this->assertTrue($locator->has('foo'));
        $this->assertFalse($locator->has('bar'));
    }
}
