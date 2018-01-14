<?php

namespace OkStuff\PhpNsq\Tests;

use OkStuff\PhpNsq\Tunnel\Config;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use PHPUnit\Framework\TestCase;

class TunnelTest extends TestCase
{
    public function testInitialized()
    {
        $config = new Config("127.0.0.1", 4150);
        $tunnel = new Tunnel($config);
        $this->assertInstanceOf(Tunnel::class, $tunnel);
    }

//    public function testWriteAndRead()
//    {
//        $in = "HEAD / HTTP/1.1\r\n";
//        $in .= "Host: www.example.com\r\n";
//        $in .= "Connection: Close\r\n\r\n";
//
//        $config = new Config("127.0.0.1", 4150);
//        $tunnel = new Tunnel($config);
//        $tunnel->write($in);
//        $this->assertEquals($tunnel->read(strlen($in)), strlen($in));
//        //TODO:
//        // $out = $connector->read();
//        // echo $out;
//        // $this->assertEquals($out, $in);
//    }
}
