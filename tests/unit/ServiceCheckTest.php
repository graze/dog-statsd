<?php

namespace Graze\DDStatsD\Test\Unit;

use Graze\DDStatsD\Client;
use Graze\DDStatsD\Test\TestCase;

class ServiceCheckTest extends TestCase
{
    public function testSimpleServiceCheck()
    {
        $this->client->serviceCheck('service.api', Client::STATUS_OK);
        $this->assertEquals('_sc|service.api|0', $this->client->getLastMessage());
    }

    public function testMetaData()
    {
        $this->client->serviceCheck(
            'service.api',
            Client::STATUS_CRITICAL,
            [
                'time'     => 12345678,
                'hostname' => 'some.host',
            ]
        );
        $this->assertEquals(
            '_sc|service.api|2|d:12345678|h:some.host',
            $this->client->getLastMessage()
        );
    }

    public function testTags()
    {
        $this->client->serviceCheck(
            'service.api',
            Client::STATUS_WARNING,
            [
                'time'     => 12345678,
                'hostname' => 'some.host',
            ],
            ['tag']
        );
        $this->assertEquals(
            '_sc|service.api|1|d:12345678|h:some.host|#tag',
            $this->client->getLastMessage()
        );
    }

    public function testMessageIsAfterTags()
    {
        $this->client->serviceCheck(
            'service.api',
            Client::STATUS_UNKNOWN,
            [
                'time'    => 12345678,
                'message' => 'some_message',
            ],
            ['tag']
        );
        $this->assertEquals(
            '_sc|service.api|3|d:12345678|#tag|m:some_message',
            $this->client->getLastMessage()
        );
    }
}
