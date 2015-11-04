<?php

namespace Graze\DDStatsD\Exception;

use Exception;
use Graze\DDStatsD\Client;

/**
 * Connection Exception Class
 */
class ConnectionException extends Exception
{
    /**
     * Client instance that threw the exception
     *
     * @var Client
     */
    protected $instance;


    /**
     * Create new instance
     *
     * @param Client    $instance Client instance that threw the exception
     * @param string    $message  Exception message
     * @param Exception $previous Previous Exception
     */
    public function __construct($instance, $message, Exception $previous = null)
    {
        $this->instance = $instance;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get Client instance that threw the exception
     *
     * @return Client Client instance
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
