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

use Graze\DogStatsD\Client;
use Graze\DogStatsD\Test\TestCase;
use ReflectionProperty;

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

    public function testDestruction()
    {
        $client = new Client();
        $client->configure([]);
        $client->increment('test', 1);
        $client = null;

        $this->assertNull($client);
    }

    public function testDestructionWithInvalidSocket()
    {
        // create a connection, kill the udp connection (without changing the socket), attempt to send, should re-connect and send again
        $client = new Client();
        $client->configure(['host' => '127.0.0.1']);

        // create the connection
        $client->increment('metric', 1);

        // close the socket
        $reflector = new ReflectionProperty(Client::class, 'socket');
        $reflector->setAccessible(true);
        $socket = $reflector->getValue($client);
        fclose($socket);

        $client = null;

        $this->assertNull($client);
    }
}
