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
            throw new ConnectionException('instance', 'Could not connect');
        } catch (ConnectionException $e) {
            $this->assertEquals('instance', $e->getInstance());
            $this->assertEquals('Could not connect', $e->getMessage());
            return;
        }
    }

    public function testConfigurationException()
    {
        try {
            throw new ConfigurationException('instance', 'Configuration error');
        } catch (ConfigurationException $e) {
            $this->assertEquals('instance', $e->getInstance());
            $this->assertEquals('Configuration error', $e->getMessage());
            return;
        }
    }
}
