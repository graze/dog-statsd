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
use Graze\DogStatsD\Exception\ConfigurationException;

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
        $this->expectExceptionMessage('Option: host is expected to be: \'string\', was: \'integer\'');

        $this->client->configure([
            'host' => 12434,
        ]);
    }

    public function testLargePortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: Port is invalid or is out of range');

        $this->client->configure([
            'port' => 65536,
        ]);
    }

    public function testStringPortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: Port is invalid or is out of range');

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

    public function testInvalidNamespace()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: namespace is expected to be: \'string\', was: \'integer\'');

        $this->client->configure([
            'namespace' => 12345,
        ]);
    }

    public function testInvalidDataDogThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: dataDog is expected to be: \'boolean\', was: \'string\'');

        $this->client->configure([
            'dataDog' => 'invalid',
        ]);
    }

    public function testInvalidTagsThrowsAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: tags is expected to be: \'array\', was: \'string\'');

        $this->client->configure([
            'tags' => 'tag,tag2',
        ]);
    }

    public function testInvalidOnErrorThrowsAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: onError \'somethingelse\' is not one of: [error,exception,ignore]');

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

    public function testTagsProcessorDoesNotAcceptOtherThings()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('supplied tag processor is not a callable');

        $this->client->configure([
            'tagProcessors' => ['a string']
        ]);
    }
}
