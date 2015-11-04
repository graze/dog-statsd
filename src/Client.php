<?php

namespace Graze\DDStatsD;

use Graze\DDStatsD\Exception\ConfigurationException;
use Graze\DDStatsD\Exception\ConnectionException;

/**
 * StatsD Client Class - Modified to support DataDogs statsd server
 *
 * @author Marc Qualie <marc@marcqualie.com>
 */
class Client
{
    const STATUS_OK       = 0;
    const STATUS_WARNING  = 1;
    const STATUS_CRITICAL = 2;
    const STATUS_UNKNOWN  = 3;

    const PRIORITY_LOW    = 'low';
    const PRIORITY_NORMAL = 'normal';

    const ALERT_ERROR   = 'error';
    const ALERT_WARNING = 'warning';
    const ALERT_INFO    = 'info';
    const ALERT_SUCCESS = 'success';

    /**
     * Instance instances array
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Instance ID
     *
     * @var string
     */
    protected $instance_id;

    /**
     * Server Host
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * Server Port
     *
     * @var integer
     */
    protected $port = 8125;

    /**
     * Last message sent to the server
     *
     * @var string
     */
    protected $message = '';

    /**
     * Class namespace
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Timeout for creating the socket connection
     *
     * @var null|float
     */
    protected $timeout;

    /**
     * Whether or not an exception should be thrown on failed connections
     *
     * @var bool
     */
    protected $throwConnectionExceptions = true;

    /**
     * Metadata for the DataDog event message
     *
     * @var array - time - Assign a timestamp to the event.
     *            - hostname - Assign a hostname to the event
     *            - key - Assign an aggregation key to th event, to group it with some others
     *            - priority - Can be 'normal' or 'low'
     *            - source - Assign a source type to the event
     *            - alert - Can be 'error', 'warning', 'info' or 'success'
     */
    protected $eventMetaData = [
        'time'     => 'd',
        'hostname' => 'h',
        'key'      => 'k',
        'priority' => 'p',
        'source'   => 's',
        'alert'    => 't',
    ];

    /**
     * @var array - time - Assign a timestamp to the service check
     *            - hostname - Assign a hostname to the service check
     */
    protected $serviceCheckMetaData = [
        'time'     => 'd',
        'hostname' => 'h',
    ];

    /**
     * @var array - message - Assign a message to the service check
     */
    protected $serviceCheckMessage = [
        'message' => 'm',
    ];

    /**
     * Singleton Reference
     *
     * @param  string $name Instance name
     *
     * @return Client Client instance
     */
    public static function instance($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new static($name);
        }
        return self::$instances[$name];
    }

    /**
     * Create a new instance
     *
     * @param string $instance_id
     */
    public function __construct($instance_id = null)
    {
        $this->instance_id = $instance_id ?: uniqid();

        if (empty($this->timeout)) {
            $this->timeout = ini_get('default_socket_timeout');
        }
    }

    /**
     * Get string value of instance
     *
     * @return string String representation of this instance
     */
    public function __toString()
    {
        return 'StatsD\Client::[' . $this->instance_id . ']';
    }

    /**
     * Initialize Connection Details
     *
     * @param array $options Configuration options
     *
     * @return Client This instance
     * @throws ConfigurationException If port is invalid
     */
    public function configure(array $options = array())
    {
        if (isset($options['host'])) {
            $this->host = $options['host'];
        }
        if (isset($options['port'])) {
            $port = (int) $options['port'];
            if (!$port || !is_numeric($port) || $port > 65535) {
                throw new ConfigurationException($this, 'Port is out of range');
            }
            $this->port = $port;
        }

        if (isset($options['namespace'])) {
            $this->namespace = $options['namespace'];
        }

        if (isset($options['timeout'])) {
            $this->timeout = $options['timeout'];
        }

        if (isset($options['throwConnectionExceptions'])) {
            $this->throwConnectionExceptions = $options['throwConnectionExceptions'];
        }

        return $this;
    }

    /**
     * Get Host
     *
     * @return string Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get Port
     *
     * @return string Port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get Namespace
     *
     * @return string Namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get Last Message
     *
     * @return string Last message sent to server
     */
    public function getLastMessage()
    {
        return $this->message;
    }

    /**
     * Increment a metric
     *
     * @param string|array $metrics    Metric(s) to increment
     * @param int          $delta      Value to decrement the metric by
     * @param int          $sampleRate Sample rate of metric
     * @param array        $tags       List of tags for this metric
     *
     * @return Client This instance
     */
    public function increment($metrics, $delta = 1, $sampleRate = 1, array $tags = [])
    {
        $metrics = (array) $metrics;
        $data = [];
        if ($sampleRate < 1) {
            foreach ($metrics as $metric) {
                if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
                    $data[$metric] = $delta . '|c|@' . $sampleRate;
                }
            }
        } else {
            foreach ($metrics as $metric) {
                $data[$metric] = $delta . '|c';
            }
        }
        return $this->send($data, $tags);
    }

    /**
     * Decrement a metric
     *
     * @param  string|array $metrics    Metric(s) to decrement
     * @param  int          $delta      Value to increment the metric by
     * @param  int          $sampleRate Sample rate of metric
     * @param  array        $tags       List of tags for this metric
     *
     * @return Client This instance
     */
    public function decrement($metrics, $delta = 1, $sampleRate = 1, array $tags = [])
    {
        return $this->increment($metrics, 0 - $delta, $sampleRate, $tags);
    }

    /**
     * Timing
     *
     * @param  string $metric Metric to track
     * @param  float  $time   Time in milliseconds
     * @param  array  $tags   List of tags for this metric
     *
     * @return bool True if data transfer is successful
     */
    public function timing($metric, $time, array $tags = [])
    {
        return $this->send(
            [
                $metric => $time . '|ms',
            ],
            $tags
        );
    }

    /**
     * Time a function
     *
     * @param  string   $metric Metric to time
     * @param  callable $func   Function to record
     * @param  array    $tags   List of tags for this metric
     *
     * @return bool True if data transfer is successful
     */
    public function time($metric, $func, array $tags = [])
    {
        $timer_start = microtime(true);
        $func();
        $timer_end = microtime(true);
        $time = round(($timer_end - $timer_start) * 1000, 4);
        return $this->timing($metric, $time, $tags);
    }


    /**
     * Gauges
     *
     * @param  string $metric Metric to gauge
     * @param  int    $value  Set the value of the gauge
     * @param  array  $tags   List of tags for this metric
     *
     * @return Client This instance
     */
    public function gauge($metric, $value, array $tags = [])
    {
        return $this->send(
            [
                $metric => $value . '|g',
            ],
            $tags
        );
    }

    /**
     * Sets - count the number of unique values passed to a key
     *
     * @param  string $metric
     * @param  mixed  $value
     * @param  array  $tags List of tags for this metric
     *
     * @return Client This instance
     */
    public function set($metric, $value, array $tags = [])
    {
        return $this->send(
            [
                $metric => $value . '|s',
            ],
            $tags
        );
    }

    /**
     * Send a event notification
     *
     * @link http://docs.datadoghq.com/guides/dogstatsd/#events
     *
     * @param  string $title    Event Title
     * @param  string $text     Event Text
     * @param  array  $metadata Set of metadata for this event:
     *                          - time - Assign a timestamp to the event.
     *                          - hostname - Assign a hostname to the event
     *                          - key - Assign an aggregation key to th event, to group it with some others
     *                          - priority - Can be 'normal' or 'low'
     *                          - source - Assign a source type to the event
     *                          - alert - Can be 'error', 'warning', 'info' or 'success'
     * @param  array  $tags     List of tags for this event
     *
     * @return Client This instance
     * @throws ConnectionException If there is a connection problem with the host
     */
    public function event($title, $text, array $metadata = [], array $tags = [])
    {
        $text = str_replace(["\r", "\n"], ['', "\\n"], $text);
        $metric = sprintf('_e{%d,%d}', strlen($title), strlen($text));
        $prefix = $this->namespace ? $this->namespace . '.' : '';
        $value = sprintf('%s|%s', $prefix . $title, $text);

        foreach ($metadata as $key => $data) {
            if (isset($this->eventMetaData[$key])) {
                $value .= sprintf('|%s:%s', $this->eventMetaData[$key], $data);
            }
        }

        $value .= $this->formatTags($tags);

        return $this->sendMessages([
            sprintf('%s:%s', $metric, $value),
        ]);
    }

    /**
     * Service Checks
     *
     * @link http://docs.datadoghq.com/guides/dogstatsd/#service-checks
     *
     * @param string $name     Name of the service
     * @param int    $status   digit corresponding to the status you’re reporting (OK = 0, WARNING = 1, CRITICAL = 2,
     *                         UNKNOWN = 3)
     * @param array  $metadata - time - Assign a timestamp to the service check
     *                         - hostname - Assign a hostname to the service check
     * @param array  $tags     List of tags for this event
     *
     * @return Client This instance
     * @throws ConnectionException If there is a connection problem with the host
     */
    public function serviceCheck($name, $status, array $metadata = [], array $tags = [])
    {
        $prefix = $this->namespace ? $this->namespace . '.' : '';
        $value = sprintf('_sc|%s|%d', $prefix . $name, $status);

        $applyMetadata = function ($metadata, $definition) use (&$value) {
            foreach ($metadata as $key => $data) {
                if (isset($definition[$key])) {
                    $value .= sprintf('|%s:%s', $definition[$key], $data);
                }
            }
        };

        $applyMetadata($metadata, $this->serviceCheckMetaData);
        $value .= $this->formatTags($tags);
        $applyMetadata($metadata, $this->serviceCheckMessage);

        return $this->sendMessages([
            $value,
        ]);
    }

    /**
     * @param array $tags A list of tags to apply to each message
     *
     * @return string
     */
    private function formatTags(array $tags = [])
    {
        if (count($tags) === 0) {
            return '';
        }

        $result = [];
        foreach ($tags as $key => $value) {
            if (is_numeric($key)) {
                $result[] = $value;
            } else {
                $result[] = sprintf('%s:%s', $key, $value);
            }
        }

        return sprintf('|#%s', implode(',', $result));
    }

    /**
     * Send Data to StatsD Server
     *
     * @param  array $data A list of messages to send to the server
     * @param  array $tags A list of tags to apply to each message
     *
     * @return Client This instance
     * @throws ConnectionException If there is a connection problem with the host
     */
    protected function send(array $data, array $tags = [])
    {
        $messages = array();
        $prefix = $this->namespace ? $this->namespace . '.' : '';
        $formattedTags = $this->formatTags($tags);
        foreach ($data as $key => $value) {
            $messages[] = $prefix . $key . ':' . $value . $formattedTags;
        }
        return $this->sendMessages($messages);
    }

    /**
     * @param array $messages
     *
     * @return Client This instance
     * @throws ConnectionException If there is a connection problem with the host
     */
    protected function sendMessages(array $messages)
    {
        $socket = @fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$socket) {
            if ($this->throwConnectionExceptions) {
                throw new ConnectionException($this, '(' . $errno . ') ' . $errstr);
            } else {
                trigger_error(
                    sprintf('StatsD server connection failed (udp://%s:%d)', $this->host, $this->port),
                    E_USER_WARNING
                );
                return;
            }
        }
        $this->message = implode("\n", $messages);
        @fwrite($socket, $this->message);
        fclose($socket);
        return $this;
    }
}
