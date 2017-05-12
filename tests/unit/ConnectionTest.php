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

use Graze\DogStatsD\Test\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * Non-integer ports are not acceptable
     *
     * @expectedException \Graze\DogStatsD\Exception\ConnectionException
     */
    public function testInvalidHost()
    {
        $this->client->configure([
            'host' => 'hostdoesnotexiststalleverlol.stupidtld',
        ]);
        $this->client->increment('test');
    }

    public function testTimeoutSettingIsUsedWhenCreatingSocketIfProvided()
    {
        $this->client->configure([
            'host'    => 'localhost',
            'timeout' => 123.425,
        ]);
        $this->assertAttributeSame(123.425, 'timeout', $this->client);
    }

    public function testCanBeConfiguredToThrowErrors()
    {
        $this->client->configure([
            'host'    => 'hostdoesnotexiststalleverlol.stupidtld',
            'onError' => 'error',
        ]);
        $handlerInvoked = false;

        $testCase = $this;

        set_error_handler(
            function ($errno, $errstr) use ($testCase, &$handlerInvoked) {
                $handlerInvoked = true;

                $testCase->assertSame(E_USER_WARNING, $errno);
                $testCase->assertSame(
                    'StatsD server connection failed (udp://hostdoesnotexiststalleverlol.stupidtld:8125)',
                    $errstr
                );
            },
            E_USER_WARNING
        );

        $this->client->increment('test');
        restore_error_handler();

        $this->assertTrue($handlerInvoked);
    }

    public function testCanBeConfiguredToNotThrowOnError()
    {
        $this->client->configure([
            'host'    => 'hostdoesnotexiststalleverlol.stupidtld',
            'onError' => 'ignore',
        ]);

        $this->client->increment('test');
        $this->assertFalse($this->client->wasSuccessful());
    }

    public function testTimeoutDefaultsToPhpIniDefaultSocketTimeout()
    {
        $this->assertAttributeEquals(ini_get('default_socket_timeout'), 'timeout', $this->client);
    }
}
