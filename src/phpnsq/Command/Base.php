<?php

namespace OkStuff\PhpNsq\Command;

use Closure;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;

class Base extends Command
{
    private static $loop;

    public function __construct($name = null)
    {
        parent::__construct($name);

        if (null === self::$loop) {
            self::$loop = Factory::create();
        }
    }

    public function runLoop()
    {
        self::$loop->run();
    }

    public function addReadStream($socket, Closure $closure)
    {
        self::$loop->addReadStream($socket, $closure);

        return $this;
    }

    public function addPeriodicTimer($interval, Closure $closure)
    {
        self::$loop->addPeriodicTimer($interval, $closure);

        return $this;
    }
}
