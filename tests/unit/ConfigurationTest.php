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
     * Large ports should be out of range
     *
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     */
    public function testLargePort()
    {
        $this->client->configure([
            'port' => 65536,
        ]);
    }

    /**
     * Non-integer ports are not acceptable
     *
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     */
    public function testStringPort()
    {
        $this->client->configure([
            'port' => 'not-integer',
        ]);
    }

    /**
     * Default Port
     */
    public function testDefaultPort()
    {
        $this->assertEquals($this->client->getPort(), 8125);
    }

    /**
     * Valid Port
     */
    public function testValidPort()
    {
        $this->client->configure([
            'port' => 1234,
        ]);
        $this->assertEquals($this->client->getPort(), 1234);
    }
}
