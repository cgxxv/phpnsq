<?php

namespace OkStuff\PhpNsq\Utils;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Logging
{
    private $handler;
    private $dirname;

    public function __construct($name, $dirname)
    {
        $this->handler = new Logger($name);
        $this->dirname = $dirname;

        $this->handler->pushHandler((new StreamHandler($this->getLogFile()))->setFormatter(new LineFormatter()));
        $this->handler->pushHandler((new StreamHandler("php://stdout"))->setFormatter(new LogFormatter(true)));
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function debug($msg, ...$context)
    {
        $this->handler->debug($msg, $context);
    }

    public function info($msg, ...$context)
    {
        $this->handler->info($msg, $context);
    }

    public function warn($msg, ...$context)
    {
        $this->handler->warning($msg, $context);
    }

    public function error($msg, ...$context)
    {
        $this->handler->error($msg, $context);
    }

    public function notice($msg, ...$context)
    {
        $this->handler->notice($msg, $context);
    }

    private function getLogFile()
    {
        $filename = $this->dirname . DIRECTORY_SEPARATOR . "phpnsq-" . date("Ymd") . ".log";
        try {
            if (!file_exists($this->dirname)) {
                mkdir($this->dirname, 0755);
            }

            if (!file_exists($filename)) {
                touch($filename);
            }
        } catch (Exception $e) {
            throw new Exception("Create `$filename` failed.");
        }

        return $filename;
    }
}
