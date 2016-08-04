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

class TagsTest extends TestCase
{
    public function testSingleTag()
    {
        $this->client->increment('test_metric', 1, 1, ['tag']);
        $this->assertEquals('test_metric:1|c|#tag', $this->client->getLastMessage());
    }

    public function testKeyValueTag()
    {
        $this->client->increment('test_metric', 1, 1, ['tag' => 'value']);
        $this->assertEquals('test_metric:1|c|#tag:value', $this->client->getLastMessage());
    }

    public function testMultipleTags()
    {
        $this->client->increment('test_metric', 1, 1, ['tag' => 'value', 'tag2', 'tag3' => 'value2']);
        $this->assertEquals('test_metric:1|c|#tag:value,tag2,tag3:value2', $this->client->getLastMessage());
    }

    public function testCoreStatsDImplementation()
    {
        $this->client->configure([
            'dataDog' => false,
        ]);
        $this->client->increment('test_metric', 1, 1, ['tag']);
        $this->assertEquals('test_metric:1|c', $this->client->getLastMessage());
    }

    public function testDefaultTagsGetAddedToRequest()
    {
        $this->client->configure([
            'tags' => ['tag1'],
        ]);
        $this->client->increment('test_metric', 1, 1, ['tag2']);
        $this->assertEquals('test_metric:1|c|#tag1,tag2', $this->client->getLastMessage());
    }
}
