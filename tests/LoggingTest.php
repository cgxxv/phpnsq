<?php

namespace OkStuff\PhpNsq\Tests;

use OkStuff\PhpNsq\Utility\Logging;
use PHPUnit\Framework\TestCase;

class LoggingTest extends TestCase
{
    public function testInitialized()
    {
        $name = "PHPNSQ";
        $log = new Logging($name, "/tmp");
        $this->assertEquals($name, $log->getHandler()->getName());
    }
}
