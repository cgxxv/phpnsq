<?php

namespace OkStuff\PHPNSQ\Tests;

use PHPUnit\Framework\TestCase;
use OkStuff\PHPNSQ\Config;

class ConfigTest extends TestCase
{
    public function testConfigInitial()
    {
        $config = new Config();
        $this->assertTrue($config->initialized);

        // foreach ($config as $key => $value) {
        //     var_dump($key, $value);
        // }
    }
}