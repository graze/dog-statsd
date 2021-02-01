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

class EnvConfigurationTest extends TestCase
{
    public function tearDown(): void
    {
        putenv('DD_AGENT_HOST=');
        putenv('DD_DOGSTATSD_PORT=');
        putenv('DD_ENTITY_ID=');
    }

    public function testHost()
    {
        putenv('DD_AGENT_HOST=127.0.0.1');
        $this->client->configure();

        $this->assertEquals('127.0.0.1', $this->client->getHost());
    }

    public function testPort()
    {
        putenv('DD_DOGSTATSD_PORT=12434');
        $this->client->configure();

        $this->assertEquals(12434, $this->client->getPort());
    }

    public function testLargePortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: Port is invalid or is out of range');

        putenv('DD_DOGSTATSD_PORT=65536');
        $this->client->configure();
    }

    public function testStringPortWillThrowAnException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Option: Port is invalid or is out of range');

        putenv('DD_DOGSTATSD_PORT=not-integer');
        $this->client->configure();
    }

    public function testTags()
    {
        putenv('DD_ENTITY_ID=f87dsf7dsf9s7d9f8');
        $this->client->configure();

        $this->client->gauge('test_metric', 456);
        $this->assertEquals('test_metric:456|g|#dd.internal.entity_id:f87dsf7dsf9s7d9f8', $this->client->getLastMessage());
    }

    public function testTagsAppended()
    {
        putenv('DD_ENTITY_ID=f87dsf7dsf9s7d9f8');
        $this->client->configure([
            'tags' => ['key' => 'value'],
        ]);

        $this->client->gauge('test_metric', 456);
        $this->assertEquals('test_metric:456|g|#key:value,dd.internal.entity_id:f87dsf7dsf9s7d9f8', $this->client->getLastMessage());
    }
}
