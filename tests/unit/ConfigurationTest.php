<?php
/**
 * This file is part of graze/dog-statsd
 *
 * Copyright (c) 2016 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/dog-statsd/blob/master/LICENSE.md
 * @link    https://github.com/graze/dog-statsd
 */

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Exception\ConfigurationException;
use Graze\DogStatsD\Test\TestCase;

class ConfigurationTest extends TestCase
{
    public function testHost()
    {
        $this->client->configure([
            'host' => '127.0.0.1',
        ]);
        $this->assertEquals('127.0.0.1', $this->client->getHost());
    }

    public function testHostInvalidTypeWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: host is expected to be: 'string', was: 'integer'");
        $this->client->configure([
            'host' => 12434,
        ]);
    }

    public function testLargePortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: Port is out of range");
        $this->client->configure([
            'port' => 65536,
        ]);
    }

    public function testStringPortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: port is expected to be: 'integer', was: 'string'");
        $this->client->configure([
            'port' => 'not-integer',
        ]);
    }

    public function testDefaultPort()
    {
        $this->assertEquals($this->client->getPort(), 8125);
    }

    public function testValidPort()
    {
        $this->client->configure([
            'port' => 1234,
        ]);
        $this->assertEquals($this->client->getPort(), 1234);
    }

    public function testInvalidNamespace()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: namespace is expected to be: 'string', was: 'integer'");
        $this->client->configure([
            'namespace' => 12345,
        ]);
    }

    public function testInvalidThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: throwExceptions is expected to be: 'boolean', was: 'string'");
        $this->client->configure([
            'throwExceptions' => 'fish',
        ]);
    }

    public function testInvalidDataDogThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: dataDog is expected to be: 'boolean', was: 'string'");
        $this->client->configure([
            'dataDog' => 'invalid',
        ]);
    }

    public function testInvalidTagsThrowsAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Option: tags is expected to be: 'array', was: 'string'");
        $this->client->configure([
            'tags' => 'tag,tag2',
        ]);
    }
}
