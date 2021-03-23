<?php

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009-2017 Fabien Potencier
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
use Customify\Vendor\Pimple\Psr11\Container as PsrContainer;
use Customify\Vendor\Pimple\Tests\Fixtures\Service;
class ContainerTest extends \Customify\Vendor\PHPUnit\Framework\TestCase
{
    public function testGetReturnsExistingService()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $psr = new \Customify\Vendor\Pimple\Psr11\Container($pimple);
        $this->assertSame($pimple['service'], $psr->get('service'));
    }
    public function testGetThrowsExceptionIfServiceIsNotFound()
    {
        $this->expectException(\Customify\Vendor\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Identifier "service" is not defined.');
        $pimple = new \Customify\Vendor\Pimple\Container();
        $psr = new \Customify\Vendor\Pimple\Psr11\Container($pimple);
        $psr->get('service');
    }
    public function testHasReturnsTrueIfServiceExists()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $pimple['service'] = function () {
            return new \Customify\Vendor\Pimple\Tests\Fixtures\Service();
        };
        $psr = new \Customify\Vendor\Pimple\Psr11\Container($pimple);
        $this->assertTrue($psr->has('service'));
    }
    public function testHasReturnsFalseIfServiceDoesNotExist()
    {
        $pimple = new \Customify\Vendor\Pimple\Container();
        $psr = new \Customify\Vendor\Pimple\Psr11\Container($pimple);
        $this->assertFalse($psr->has('service'));
    }
}
