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

class TimerTest extends TestCase
{
    public function testTiming()
    {
        $this->client->timing('test_metric', 123);
        $this->assertEquals('test_metric:123|ms', $this->client->getLastMessage());
    }

    public function testFunctionTiming()
    {
        $this->client->time('test_metric', function () {
            usleep(100000);
        });
        $this->assertRegExp('/test_metric:1[0-9]{2}\.[0-9]+\|ms/', $this->client->getLastMessage());
    }

    public function testTags()
    {
        $this->client->timing('test_metric', 123, ['tag']);
        $this->assertEquals('test_metric:123|ms|#tag', $this->client->getLastMessage());
    }
}
