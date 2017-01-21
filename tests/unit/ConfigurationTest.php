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

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: host is expected to be: 'string', was: 'integer'
     */
    public function testHostInvalidTypeWillThrowAnException()
    {
        $this->client->configure([
            'host' => 12434,
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: Port is out of range
     */
    public function testLargePortWillThrowAnException()
    {
        $this->client->configure([
            'port' => 65536,
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: port is expected to be: 'integer', was: 'string'
     */
    public function testStringPortWillThrowAnException()
    {
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

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: namespace is expected to be: 'string', was: 'integer'
     */
    public function testInvalidNamespace()
    {
        $this->client->configure([
            'namespace' => 12345,
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: throwExceptions is expected to be: 'boolean', was: 'string'
     */
    public function testInvalidThrowAnException()
    {
        $this->client->configure([
            'throwExceptions' => 'fish',
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: dataDog is expected to be: 'boolean', was: 'string'
     */
    public function testInvalidDataDogThrowAnException()
    {
        $this->client->configure([
            'dataDog' => 'invalid',
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: tags is expected to be: 'array', was: 'string'
     */
    public function testInvalidTagsThrowsAnException()
    {
        $this->client->configure([
            'tags' => 'tag,tag2',
        ]);
    }
}
