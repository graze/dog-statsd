<?php

namespace Graze\DogStatsD\Test\Unit;

use Graze\DogStatsD\Stream\StreamWriter;
use Graze\DogStatsD\Test\TestCase;
use ReflectionClass;
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

    public function testDestructionWillCloseTheSocket()
    {
        $writer = new StreamWriter();
        $writer->write('test');

        // close the socket
        $reflector = new ReflectionProperty(StreamWriter::class, 'socket');
        $reflector->setAccessible(true);
        $socket = $reflector->getValue($writer);

        $this->assertTrue(is_resource($socket));
        $writer = null;

        $this->assertNull($writer);
        $this->assertFalse(is_resource($socket));
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

        $socket = $reflector->getValue($writer);
        $this->assertTrue(is_resource($socket));
    }

    public function testWhenItWillRetryIsExponential()
    {
        $writer = new StreamWriter('test', 'doesnotexist.tld', 8125, StreamWriter::ON_ERROR_IGNORE);

        // make connect public to test lots of attempts to connect
        $class = new ReflectionClass(StreamWriter::class);
        $connect = $class->getMethod('connect');
        $connect->setAccessible(true);

        $connect->invoke($writer);
        $connect->invoke($writer);
        $connect->invoke($writer);
        $connect->invoke($writer);
        $connect->invoke($writer);
        $connect->invoke($writer);
        $connect->invoke($writer);

        $this->assertGreaterThan(microtime(true) + 2, $writer->waitTill);
        $this->assertEquals(7, $writer->numFails);

        $writer->write('test');

        $this->assertEquals(7, $writer->numFails, 'attempting to write with a back-off should not try and connect');
    }

    public function testLongMessage()
    {
        $writer = new StreamWriter();
        $this->assertTrue($writer->write(str_repeat('x', 10000)));
    }
}
