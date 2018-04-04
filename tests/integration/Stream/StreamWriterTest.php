<?php

namespace Graze\DogStatsD\Test\Integration\Stream;

use Graze\DogStatsD\Stream\StreamWriter;
use Graze\DogStatsD\Test\TestCase;
use ReflectionProperty;

class StreamWriterTest extends TestCase
{
    /** @var StreamWriter */
    private $writer;
    /** @var resource */
    private $socket;

    public function setUp()
    {
        parent::setUp();

        $this->writer = new StreamWriter('test', 'python-echo');

        $this->writer->write('');

        // get the socket
        $reflector = new ReflectionProperty(StreamWriter::class, 'socket');
        $reflector->setAccessible(true);
        /** @var resource $socket */
        $this->socket = $reflector->getValue($this->writer);
    }

    /**
     * @param int $len
     *
     * @return string
     */
    private function readSocket($len)
    {
        $time = microtime(true);
        $out = '';
        do {
            $out .= fread($this->socket, $len);
        } while (strlen($out) < $len && microtime(true) < $time + 1);

        return $out;
    }

    public function testEcho()
    {
        $this->assertTrue($this->writer->write('echo'));
        $out = $this->readSocket(4);

        $this->assertEquals('echo', $out);
    }

    public function testLongMessage()
    {
        $this->assertTrue($this->writer->write(str_repeat('x', 10000)));
        $out = $this->readSocket(20000);

        $this->assertEquals(10000, strlen($out));
        $this->assertEquals(str_repeat('x', 10000), $out);
    }
}
