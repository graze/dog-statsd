<?php

namespace Graze\DogStatsD\Test;

use Graze\DogStatsD\Client;
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
