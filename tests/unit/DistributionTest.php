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

class DistributionTest extends TestCase
{
    public function testDistribution()
    {
        $this->client->distribution('test_metric', 10);
        $this->assertEquals('test_metric:10|d', $this->client->getLastMessage());

        $this->client->distribution('test_metric', 1.2);
        $this->assertEquals('test_metric:1.2|d', $this->client->getLastMessage());
    }

    public function testDistributionSample()
    {
        while ($this->client->getLastMessage() === '') {
            $this->client->distribution('test_metric', 5, 0.75);
        }
        $this->assertEquals('test_metric:5|d|@0.75', $this->client->getLastMessage());
    }

    public function testDistributionSampleFailure()
    {
        $this->client->distribution('test_metric', 5, 0);
        $this->assertEquals('', $this->client->getLastMessage());
    }

    public function testDistributionTags()
    {
        $this->client->distribution('test_metric', 10, 1.0, []);
        $this->assertEquals('test_metric:10|d', $this->client->getLastMessage());

        $this->client->distribution('test_metric', 10, 1.0, ['tag1']);
        $this->assertEquals('test_metric:10|d|#tag1', $this->client->getLastMessage());

        $this->client->distribution('test_metric', 10, 1.0, ['tag1', 'tag2']);
        $this->assertEquals('test_metric:10|d|#tag1,tag2', $this->client->getLastMessage());

        $this->client->distribution('test_metric', 10, 1.0, ['tag1', 'tag1']);
        $this->assertEquals('test_metric:10|d|#tag1,tag1', $this->client->getLastMessage());
    }
}
