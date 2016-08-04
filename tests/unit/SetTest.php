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

class SetTest extends TestCase
{
    public function testSet()
    {
        $this->client->set('test_metric', 456);
        $this->assertEquals('test_metric:456|s', $this->client->getLastMessage());
    }

    public function testTags()
    {
        $this->client->set('test_metric', 456, ['tag']);
        $this->assertEquals('test_metric:456|s|#tag', $this->client->getLastMessage());
    }
}
