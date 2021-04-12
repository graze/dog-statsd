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

namespace Graze\DogStatsD\Stream;

use Graze\DogStatsD\Exception\ConnectionException;

/**
 * StreamWriter will attempt to write a message to a udp socket.
 *
 * If the connection fails, it will never try and reconnect to prevent application blocking
 */
class StreamWriter implements WriterInterface
{
    /**
     * Seconds to wait (as a base) for exponential back-off on connection
     *
     * minDelay = RETRY_INTERVAL * (2 ^ num_failed_attempts)
     *
     * e.g.
     * 0, 0.1 0.2 0.4 0.8 1.6 3.2 6.4 12.8 25.6 51.2 102.4 etc...
     */
    const RETRY_INTERVAL = 0.1;

    /**
     * Maximum length of a string to send
     */
    const MAX_SEND_LENGTH = 1024;

    const ON_ERROR_ERROR     = 'error';
    const ON_ERROR_EXCEPTION = 'exception';
    const ON_ERROR_IGNORE    = 'ignore';

    /** @var resource|null */
    protected $socket;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $onError;
    /** @var float|null */
    private $timeout;
    /** @var string */
    private $instance;
    /** @var int */
    private $numFails = 0;
    /** @var float */
    private $waitTill = 0.0;

    /**
     * @param string     $instance
     * @param string     $host
     * @param int        $port
     * @param string     $onError What to do on connection error
     * @param float|null $timeout
     */
    public function __construct(
        $instance = 'writer',
        $host = '127.0.0.1',
        $port = 8125,
        $onError = self::ON_ERROR_EXCEPTION,
        $timeout = null
    ) {
        $this->instance = $instance;
        $this->host = $host;
        $this->port = $port;
        $this->onError = $onError;
        $this->timeout = $timeout;
    }

    public function __destruct()
    {
        if ($this->socket && is_resource($this->socket)) {
            // the reason for this failing is that it is already closed, so ignore the result and not messing with
            // parent classes
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
            $totalLength = strlen($message);
            $retries = 1;
            $response = 0;
            for ($written = 0; $written < $totalLength; $written += $response) {
                $response = @fwrite($this->socket, substr($message, $written), static::MAX_SEND_LENGTH);
                if ($response === false) {
                    if ($retries-- > 0) {
                        $this->socket = $this->connect();
                        $response = 0;
                    } else {
                        return false;
                    }
                } else {
                    $retries = 1;
                }
            }
            return ($written === $totalLength);
        }
        return false;
    }

    /**
     * Ensure that we are currently connected to the socket
     */
    protected function ensureConnection()
    {
        if ((!$this->socket || !is_resource($this->socket)) && $this->canConnect()) {
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
            $this->waitTill = microtime(true) + (static::RETRY_INTERVAL * (pow(2, $this->numFails++)));

            switch ($this->onError) {
                case static::ON_ERROR_ERROR:
                    trigger_error(
                        sprintf('StatsD server connection failed (udp://%s:%d)', $this->host, $this->port),
                        E_USER_WARNING
                    );
                    break;
                case static::ON_ERROR_EXCEPTION:
                    throw new ConnectionException($this->instance, '(' . $errno . ') ' . $errstr);
            }
        } else {
            $this->numFails = 0;
            $this->waitTill = 0.0;

            $sec = (int) $this->timeout;
            $ms = (int) (($this->timeout - $sec) * 1000);
            stream_set_timeout($socket, $sec, $ms);
        }
        return $socket;
    }
}
