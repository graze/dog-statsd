<?php

namespace Graze\DDStatsD\Test\Unit;
use Graze\DDStatsD\Exception\ConnectionException;
use Graze\DDStatsD\Exception\ConfigurationException;
use Graze\DDStatsD\Client;
use Graze\DDStatsD\Test\TestCase;

class ExceptionTest extends TestCase
{
    public function testConnectionException()
    {
        try {
            throw new ConnectionException($this->client, 'Could not connect');
        } catch (ConnectionException $e) {
            $client = $e->getInstance();
            $this->assertTrue($client instanceof Client);
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
            $this->assertTrue($client instanceof Client);
            $this->assertEquals('Configuration error', $e->getMessage());
            return;
        }
    }
}
