<?php

namespace Graze\DogStatsD\Stream;

interface WriterInterface
{
    /**
     * @param string $message
     *
     * @return bool
     */
    public function write($message);
}
