<?php

namespace Graze\DDStatsD\Test;

use Graze\DDStatsD\Client;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = new Client();
        $this->client->configure();
    }
}
