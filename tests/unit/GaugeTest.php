<?php

namespace Graze\DDStatsD\Test\Unit;

use Graze\DDStatsD\Test\TestCase;

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
