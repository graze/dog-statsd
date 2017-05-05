<?php

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Stream\StreamWriter;
use Graze\DogStatsD\Test\TestCase;
use ReflectionProperty;

class StreamWriterTest extends TestCase
{

    public function testDestructionWithInvalidSocket()
    {
        $writer = new StreamWriter();
        $writer->write('test');

        // close the socket
        $reflector = new ReflectionProperty(StreamWriter::class, 'socket');
        $reflector->setAccessible(true);
        $socket = $reflector->getValue($writer);
        fclose($socket);

        $writer = null;

        $this->assertNull($writer);
    }

    public function testSendFailureWillReconnect()
    {
        $writer = new StreamWriter();
        $writer->write('test');

        // close the socket
        $reflector = new ReflectionProperty(StreamWriter::class, 'socket');
        $reflector->setAccessible(true);
        $socket = $reflector->getValue($writer);
        fclose($socket);

        $this->assertTrue($writer->write('reconnect'));

        $this->assertAttributeInternalType('resource', 'socket', $writer);
    }

    public function testWhenItWillRetryIsExponential()
    {
        $writer = new StreamWriter('test', 'doesnotexist.tld', 8125, StreamWriter::ON_ERROR_IGNORE);
        $this->assertFalse($writer->write('test'));
        $this->assertFalse($writer->write('test'));
        $this->assertFalse($writer->write('test'));
        $this->assertFalse($writer->write('test'));
        $this->assertFalse($writer->write('test'));
        $this->assertFalse($writer->write('test'));

        $this->assertAttributeGreaterThan(microtime(true), 'waitTill', $writer);
        $this->assertAttributeLessThan(6, 'numFails', $writer);
    }
}
