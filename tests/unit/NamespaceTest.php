<?php

namespace Graze\DDStatsD\Test\Unit;

use Graze\DDStatsD\Client;
use Graze\DDStatsD\Test\TestCase;

class NamespaceTest extends TestCase
{
    public function testNamespace()
    {
        $this->client->configure(array(
            'host' => '127.0.0.1',
            'port' => 8125,
            'namespace' => 'test_namespace'
        ));
        $this->assertEquals('test_namespace', $this->client->getNamespace());
    }

    public function testNamespaceIncrement()
    {
        $this->client->configure(array(
            'host' => '127.0.0.1',
            'port' => 8125,
            'namespace' => 'test_namespace'
        ));
        $this->client->increment('test_metric');
        $this->assertEquals('test_namespace.test_metric:1|c', $this->client->getLastMessage());
    }

    public function testNamespaceEvent()
    {
        $this->client->configure(array(
            'host' => '127.0.0.1',
            'port' => 8125,
            'namespace' => 'test_namespace'
        ));
        $this->client->event('some_title','textAndThings');
        $this->assertEquals('_e{10,13}:test_namespace.some_title|textAndThings', $this->client->getLastMessage());
    }

    public function testNamespaceSimpleService()
    {
        $this->client->configure(array(
            'host' => '127.0.0.1',
            'port' => 8125,
            'namespace' => 'test_namespace'
        ));
        $this->client->serviceCheck('service.api', Client::STATUS_OK);
        $this->assertEquals('_sc|test_namespace.service.api|0', $this->client->getLastMessage());
    }
}
