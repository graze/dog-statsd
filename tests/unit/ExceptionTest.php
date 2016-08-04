<?php

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Client;
use Graze\DogStatsD\Exception\ConfigurationException;
use Graze\DogStatsD\Exception\ConnectionException;
use Graze\DogStatsD\Test\TestCase;

class ExceptionTest extends TestCase
{
    public function testConnectionException()
    {
        try {
            throw new ConnectionException($this->client, 'Could not connect');
        } catch (ConnectionException $e) {
            $client = $e->getInstance();
            $this->assertInstanceOf(Client::class, $client);
            $this->assertEquals('Could not connect', $e->getMessage());
            return;
        }
    }

    public function testConfigurationException()
    {
        try {
            throw new ConfigurationException($this->client, 'Configuration error');
        } catch (ConfigurationException $e) {
            $client = $e->getInstance();
            $this->assertInstanceOf(Client::class, $client);
            $this->assertEquals('Configuration error', $e->getMessage());
            return;
        }
    }
}
