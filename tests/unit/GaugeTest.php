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

class GaugeTest extends TestCase
{
    public function testGauge()
    {
        $this->client->gauge('test_metric', 456);
        $this->assertEquals('test_metric:456|g', $this->client->getLastMessage());
    }

    public function testTags()
    {
        $this->client->gauge('test_metric', 456, ['tag']);
        $this->assertEquals('test_metric:456|g|#tag', $this->client->getLastMessage());
    }
}
