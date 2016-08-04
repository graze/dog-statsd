<?php

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Client;
use Graze\DogStatsD\Test\TestCase;

class ClientTest extends TestCase
{
    public function testNewInstance()
    {
        $client = new Client();
        $this->assertTrue($client instanceof Client);
        $this->assertRegExp('/^DogStatsD\\\Client::\[[a-zA-Z0-9]+\]$/', (String) $client);
    }

    public function testStaticInstance()
    {
        $client1 = Client::instance('instance1');
        $this->assertTrue($client1 instanceof Client);
        $client2 = Client::instance('instance2');
        $client3 = Client::instance('instance1');
        $this->assertEquals('DogStatsD\Client::[instance2]', (String) $client2);
        $this->assertFalse((String) $client1 === (String) $client2);
        $this->assertTrue((String) $client1 === (String) $client3);
    }
}
