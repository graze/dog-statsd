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
            'timeout' => 123,
        ]);
        $this->assertAttributeSame(123, 'timeout', $this->client);
    }

    public function testCanBeConfiguredNotToThrowConnectionExceptions()
    {
        $this->client->configure([
            'host'            => 'hostdoesnotexiststalleverlol.stupidtld',
            'throwExceptions' => false,
        ]);
        $handlerInvoked = false;

        $testCase = $this;

        set_error_handler(
            function ($errno, $errstr, $errfile, $errline, $errcontext) use ($testCase, &$handlerInvoked) {
                $handlerInvoked = true;

                $testCase->assertSame(E_USER_WARNING, $errno);
                $testCase->assertSame(
                    'StatsD server connection failed (udp://hostdoesnotexiststalleverlol.stupidtld:8125)',
                    $errstr
                );
                $testCase->assertSame(realpath(__DIR__ . '/../../src/Client.php'), $errfile);
            },
            E_USER_WARNING
        );

        $this->client->increment('test');
        restore_error_handler();

        $this->assertTrue($handlerInvoked);
    }

    public function testTimeoutDefaultsToPhpIniDefaultSocketTimeout()
    {
        $this->assertAttributeEquals(ini_get('default_socket_timeout'), 'timeout', $this->client);
    }
}
