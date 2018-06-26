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

class TagProcessorTest extends TestCase
{
    public function testSingleProcessor()
    {
        $this->client->addTagProcessor(function (array $tags) {
            $tags['custom'] = 1;
            return $tags;
        });
        $this->client->increment('test_metric', 1, 1, ['tag']);
        $this->assertEquals('test_metric:1|c|#tag,custom:1', $this->client->getLastMessage());
    }

    public function testMultipleProcessors()
    {
        $this->client->addTagProcessor(function (array $tags) {
            $tags['custom'] = 1;
            return $tags;
        });
        $this->client->addTagProcessor(function (array $tags) {
            $this->assertArrayHasKey(
                'custom',
                $tags,
                'ensure that the processors get processed in the order they are attached to the client'
            );
            $tags['second'] = 2;
            return $tags;
        });
        $this->client->increment('test_metric', 1, 1, ['tag' => 'value']);
        $this->assertEquals('test_metric:1|c|#tag:value,custom:1,second:2', $this->client->getLastMessage());
    }
}
