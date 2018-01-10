<?php

namespace OkStuff\PHPNSQ\Tests;

use PHPUnit\Framework\TestCase;
use OkStuff\PHPNSQ\Config;
use OkStuff\PHPNSQ\Connector;

class ConnectorTest extends TestCase
{
    public function testInitialized()
    {
        $config = new Config();
        $connector = new Connector('127.0.0.1', 80, $config, null);
        $this->assertInstanceOf(Connector::class, $connector);
    }

    public function testWriteAndRead()
    {
        $in = "HEAD / HTTP/1.1\r\n";
        $in .= "Host: www.example.com\r\n";
        $in .= "Connection: Close\r\n\r\n";

        $config = new Config();
        $connector = new Connector('127.0.0.1', 80, $config, null);
        $this->assertEquals($connector->write($in), strlen($in));
        //TODO:
        // $out = $connector->read();
        // echo $out;
        // $this->assertEquals($out, $in);
    }
}