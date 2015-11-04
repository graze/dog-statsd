<?php

namespace Graze\DDStatsD\Test\Unit;

use Graze\DDStatsD\Test\TestCase;

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
