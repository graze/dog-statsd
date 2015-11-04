<?php

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
        $this->client->configure(array(
            'host' => '127.0.0.1',
            'port' => 8125,
            'dataDog' => false
        ));
        $this->client->increment('test_metric', 1, 1, ['tag']);
        $this->assertEquals('test_metric:1|c', $this->client->getLastMessage());
    }
}
