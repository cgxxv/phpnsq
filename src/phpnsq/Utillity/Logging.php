<?php

namespace OkStuff\PhpNsq\Utility;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OkStuff\PhpNsq\Internals\CustomerLineFormatter;

class Logging
{
    private $handler;
    private $dirname;

    public function __construct($name, $dirname)
    {
        $this->handler = new Logger($name);
        $this->dirname = $dirname;

        $this->handler->pushHandler((new StreamHandler($this->getLogFile()))->setFormatter(new CustomerLineFormatter(false, false, true)));
        $this->handler->pushHandler((new StreamHandler("php://stdout"))->setFormatter(new CustomerLineFormatter(true, true, true)));
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function debug($msg, ...$context)
    {
        $this->handler->debug($msg, [var_export($context, true)]);
    }

    public function info($msg, ...$context)
    {
        $this->handler->info($msg, [var_export($context, true)]);
    }

    public function warn($msg, ...$context)
    {
        $this->handler->warn($msg, [var_export($context, true)]);
    }

    public function error($msg, ...$context)
    {
        $this->handler->error($msg, [var_export($context, true)]);
    }

    private function getLogFile()
    {
        if (!file_exists($this->dirname)) {
            mkdir($this->dirname, 0755);
        }

        $filename = $this->dirname . DIRECTORY_SEPARATOR . date("Ymd") . ".log";
        if (!file_exists($filename)) {
            touch($filename);
        }

        return $filename;
    }
}
