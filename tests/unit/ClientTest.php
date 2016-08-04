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
