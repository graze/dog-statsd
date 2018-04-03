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

use Graze\DogStatsD\Test\TestCase;
use TypeError;

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
     * @expectedExceptionMessage Option: Port is invalid or is out of range
     */
    public function testLargePortWillThrowAnException()
    {
        $this->client->configure([
            'port' => 65536,
        ]);
    }

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: Port is invalid or is out of range
     */
    public function testStringPortWillThrowAnException()
    {
        $this->client->configure([
            'port' => 'not-integer',
        ]);
    }

    public function testValidStringPort()
    {
        $this->client->configure([
            'port' => '1234',
        ]);
        $this->assertEquals(1234, $this->client->getPort());
    }

    public function testDefaultPort()
    {
        $this->assertEquals(8125, $this->client->getPort());
    }

    public function testValidPort()
    {
        $this->client->configure([
            'port' => 1234,
        ]);
        $this->assertEquals(1234, $this->client->getPort());
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

    /**
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     * @expectedExceptionMessage Option: onError 'somethingelse' is not one of: [error,exception,ignore]
     */
    public function testInvalidOnErrorThrowsAnException()
    {
        $this->client->configure([
            'onError' => 'somethingelse',
        ]);
    }

    public function testOnErrorConfiguration()
    {
        // exception is default
        $this->assertAttributeEquals('exception', 'onError', $this->client);

        $this->client->configure(['onError' => 'error']);
        $this->assertAttributeEquals('error', 'onError', $this->client);

        $this->client->configure(['onError' => 'exception']);
        $this->assertAttributeEquals('exception', 'onError', $this->client);

        $this->client->configure(['onError' => 'ignore']);
        $this->assertAttributeEquals('ignore', 'onError', $this->client);
    }

    public function testTagsProcessorAcceptsCallable()
    {
        $processor = function (array $tags) {
            return $tags;
        };
        $this->client->configure([
            'tagProcessors' => [$processor],
        ]);
        $this->assertAttributeEquals([$processor], 'tagProcessors', $this->client);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Graze\DogStatsD\Client::addTagProcessor() must be callable, string given
     */
    public function testTagsProcessorDoesNotAcceptOtherThings()
    {
        $this->client->configure(['tagProcessors' => ['a string']]);
    }
}
