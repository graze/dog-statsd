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

namespace Graze\DogStatsD;

use Graze\DogStatsD\Exception\ConnectionException;
use Graze\DogStatsD\Stream\WriterInterface;

/**
 * WriteStream will attempt to write a message to a udp socket.
 *
 * If the connection fails, it will never try and reconnect to prevent application blocking
 */
class WriteStream implements WriterInterface
{
    /**
     * Seconds to wait (as a base) for exponential back-off on connection
     *
     * minDelay = RETRY_INTERVAL * (2 ^ num_failed_attempts)
     *
     * e.g.
     * 0.1 0.2 0.4 0.8 1.6 3.2 6.4 12.8 25.6 51.2 102.4 etc...
     */
    const RETRY_INTERVAL = 0.1;

    /** @var resource|null */
    protected $socket;
    /** @var string */
    private $host;
    /** @var string */
    private $port;
    /** @var bool */
    private $throwExceptions;
    /** @var float|null */
    private $timeout;
    /** @var Client */
    private $client;
    /** @var int */
    private $numFails = 0;
    /** @var float */
    private $waitTill = 0.0;

    /**
     * @param Client     $client
     * @param string     $host
     * @param string     $port
     * @param bool       $throwExceptions
     * @param float|null $timeout
     */
    public function __construct(Client $client, $host, $port, $throwExceptions = false, $timeout = null)
    {
        $this->client = $client;
        $this->host = $host;
        $this->port = $port;
        $this->throwExceptions = $throwExceptions;
        $this->timeout = $timeout;
    }

    public function __destruct()
    {
        if ($this->socket) {
            @fclose($this->socket);
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function write($message)
    {
        $this->ensureConnection();
        if ($this->socket) {
            if (@fwrite($this->socket, $message) === false) {
                // attempt to re-send on socket resource failure
                $this->socket = $this->connect();
                if (!$this->socket) {
                    return (@fwrite($this->socket, $message) !== false);
                }
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Ensure that we are currently connected to the socket
     */
    protected function ensureConnection()
    {
        if (!$this->socket && $this->canConnect()) {
            $this->socket = $this->connect();
        }
    }

    /**
     * @return bool
     */
    protected function canConnect()
    {
        return (microtime(true) > $this->waitTill);
    }

    /**
     * Attempt to connect to a stream
     *
     * @return null|resource
     */
    protected function connect()
    {
        $socket = @fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, $this->timeout);
        if ($socket === false) {
            $this->numFails++;
            $this->waitTill = microtime(true) + (static::RETRY_INTERVAL * (pow(2, $this->numFails)));

            if ($this->throwExceptions) {
                throw new ConnectionException($this->client, '(' . $errno . ') ' . $errstr);
            } else {
                trigger_error(
                    sprintf('StatsD server connection failed (udp://%s:%d)', $this->host, $this->port),
                    E_USER_WARNING
                );
            }
        } else {
            $this->numFails = 0;
            $this->waitTill = 0;

            $sec = (int) $this->timeout;
            $ms = (int) (($this->timeout - $sec) * 1000);
            stream_set_timeout($socket, $sec, $ms);
        }
        return $socket;
    }
}
