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

use Graze\DogStatsD\Client;
use Graze\DogStatsD\Exception\ConfigurationException;
use Graze\DogStatsD\Exception\ConnectionException;
use Graze\DogStatsD\Test\TestCase;

class ExceptionTest extends TestCase
{
    public function testConnectionException()
    {
        try {
            throw new ConnectionException($this->client, 'Could not connect');
        } catch (ConnectionException $e) {
            $client = $e->getInstance();
            $this->assertInstanceOf(Client::class, $client);
            $this->assertEquals('Could not connect', $e->getMessage());
            return;
        }
    }

    public function testConfigurationException()
    {
        try {
            throw new ConfigurationException($this->client, 'Configuration error');
        } catch (ConfigurationException $e) {
            $client = $e->getInstance();
            $this->assertInstanceOf(Client::class, $client);
            $this->assertEquals('Configuration error', $e->getMessage());
            return;
        }
    }
}
