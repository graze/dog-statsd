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

namespace Graze\DogStatsD\Test\Benchmark;

use Graze\DogStatsD\Client;
use Graze\DogStatsD\Test\TestCase;

class SendBench
{
    /**
     * @var resource|null
     */
    private $socket = null;

    /**
     * @Iterations(100)
     * @Revs(50)
     */
    public function benchPersistentConnection()
    {
        $message = "some stuff in here";
        if (is_null($this->socket)) {
            $this->socket = @fsockopen('udp://127.0.0.1', 8125, $errno, $errstr);
        }
        @fwrite($this->socket, $message);
    }

    /**
     * @Iterations(100)
     * @Revs(50)
     */
    public function benchNewConnection()
    {
        $message = "some stuff in here";
        $socket = @fsockopen('udp://127.0.0.1', 8125, $errno, $errstr);
        fwrite($socket, $message);
        fclose($socket);
    }
}
