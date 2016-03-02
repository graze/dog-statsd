<?php

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Test\TestCase;

class ConfigurationTest extends TestCase
{
    public function testHost()
    {
        $this->client->configure([
            'host' => '127.0.0.1'
        ]);
        $this->assertEquals('127.0.0.1', $this->client->getHost());
    }

    /**
     * Large ports should be out of range
     *
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     */
    public function testLargePort()
    {
        $this->client->configure(array(
            'port' => 65536,
        ));
    }


    /**
     * Non-integer ports are not acceptable
     *
     * @expectedException \Graze\DogStatsD\Exception\ConfigurationException
     */
    public function testStringPort()
    {
        $this->client->configure(array(
            'port' => 'not-integer',
        ));
    }


    /**
     * Default Port
     */
    public function testDefaultPort()
    {
        $this->assertEquals($this->client->getPort(), 8125);
    }


    /**
     * Valid Port
     */
    public function testValidPort()
    {
        $this->client->configure(array(
            'port' => 1234,
        ));
        $this->assertEquals($this->client->getPort(), 1234);
    }
}
