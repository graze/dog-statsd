<?php

namespace Graze\DDStatsD\Test\Unit;

use Graze\DDStatsD\Client;
use Graze\DDStatsD\Test\TestCase;

class EventTest extends TestCase
{
    public function testSimpleEvent()
    {
        $this->client->event('some_title', 'textAndThings');
        $this->assertEquals('_e{10,13}:some_title|textAndThings', $this->client->getLastMessage());
    }

    public function testEventMetadata()
    {
        $this->client->event(
            'some_title',
            'textAndThings',
            [
                'time'     => 12345678,
                'hostname' => 'some.host',
                'key'      => 'someKey',
                'priority' => Client::PRIORITY_LOW,
                'source'   => 'space',
                'alert'    => Client::ALERT_INFO,
            ]
        );
        $this->assertEquals(
            '_e{10,13}:some_title|textAndThings|d:12345678|h:some.host|k:someKey|p:low|s:space|t:info',
            $this->client->getLastMessage()
        );
    }

    public function testEventTags()
    {
        $this->client->event(
            'some_title',
            'textAndThings',
            [
                'time' => 12345678,
            ],
            ['tag']
        );
        $this->assertEquals(
            '_e{10,13}:some_title|textAndThings|d:12345678|#tag',
            $this->client->getLastMessage()
        );
    }

    public function testEventTextReplacesNewLines()
    {
        $this->client->event('some_title', "LongText\rAnd\nStuff");
        $this->assertEquals("_e{10,18}:some_title|LongTextAnd\\nStuff", $this->client->getLastMessage());
    }
}
