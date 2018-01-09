<?php

namespace OkStuff\PHPNSQ\Tests;

use PHPUnit\Framework\TestCase;
use OkStuff\PHPNSQ\Config;

class ConfigTest extends TestCase
{
    public function testInitialized()
    {
        $config = new Config();
        $this->assertTrue($config->initialized);

        // foreach ($config as $key => $value) {
        //     var_dump($key, $value);
        // }
    }

    public function testValidation()
    {
        $config = new Config();
        $this->assertTrue($config->validate());
    }
}