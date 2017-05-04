<?php
/**
 * This file is part of graze/dog-statsd
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
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

class SendTest extends TestCase
{
    public function testSendFailure()
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

        $client->increment('reconnect', 1);
        $this->assertEquals('reconnect:1|c', $client->getLastMessage());

        $this->assertAttributeInternalType('resource', 'socket', $client);
    }
}
