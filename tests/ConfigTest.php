<?php

namespace OkStuff\PhpNsq\Tests;

use OkStuff\PhpNsq\Tunnel\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testInitialized()
    {
        $config = new Config("127.0.0.1", 4150);
        $this->assertTrue($config->initialized);

        // foreach ($config as $key => $value) {
        //     var_dump($key, $value);
        // }
    }

    public function testValidation()
    {
        $config = new Config("127.0.0.1", 4150);
        $this->assertTrue($config->validate());
    }
}
