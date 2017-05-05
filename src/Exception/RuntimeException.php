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

namespace Graze\DogStatsD\Exception;

use Exception;
use Graze\DogStatsD\Client;

class RuntimeException extends \RuntimeException
{
    /**
     * Client instance id that threw the exception
     *
     * @var string
     */
    protected $instance;

    /**
     * Create new instance
     *
     * @param string         $instance Client instance that threw the exception
     * @param string|null    $message  Exception message
     * @param Exception|null $previous Previous Exception
     */
    public function __construct($instance, $message = '', Exception $previous = null)
    {
        $this->instance = $instance;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get instance name that threw the exception
     *
     * @return string Instance name
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
