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

class CounterTest extends TestCase
{
    public function testIncrement()
    {
        $this->client->increment('test_metric', 1);
        $this->assertEquals('test_metric:1|c', $this->client->getLastMessage());
    }

    public function testIncrementDelta()
    {
        $this->client->increment('test_metric', 2);
        $this->assertEquals('test_metric:2|c', $this->client->getLastMessage());
    }

    public function testIncrementSample()
    {
        while ($this->client->getLastMessage() === '') {
            $this->client->increment('test_metric', 1, 0.75);
        }
        $this->assertEquals('test_metric:1|c|@0.75', $this->client->getLastMessage());
    }

    public function testIncrementSampleFailure()
    {
        $this->client->increment('test_metric', 1, 0);
        $this->assertEquals('', $this->client->getLastMessage());
    }

    public function testDecrement()
    {
        $this->client->decrement('test_metric');
        $this->assertEquals('test_metric:-1|c', $this->client->getLastMessage());
    }

    public function testDecrementDelta()
    {
        $this->client->decrement('test_metric', 3);
        $this->assertEquals('test_metric:-3|c', $this->client->getLastMessage());
    }

    public function testIncrementTags()
    {
        $this->client->increment('test_metric', 1, 1, ['tag']);
        $this->assertEquals('test_metric:1|c|#tag', $this->client->getLastMessage());
    }
}
