<?php

namespace OkStuff\PhpNsq\Cmd;

use Closure;
use OkStuff\PhpNsq\PhpNsq;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;

class Base extends Command
{
    protected static $phpnsq;
    private static   $loop;

    public function __construct(array $config = null, $name = null)
    {
        parent::__construct($name);

        self::$loop   = Factory::create();
        self::$phpnsq = new PhpNsq($config);
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
