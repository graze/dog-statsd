<?php

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
            usleep(50000);
        });
        $this->assertRegExp('/test_metric:5[0-9]{1}\.[0-9]+\|ms/', $this->client->getLastMessage());
    }

    public function testTags()
    {
        $this->client->timing('test_metric', 123, ['tag']);
        $this->assertEquals('test_metric:123|ms|#tag', $this->client->getLastMessage());
    }
}
