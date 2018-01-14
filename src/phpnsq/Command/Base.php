<?php

namespace OkStuff\PhpNsq\Command;

use Closure;
use React\EventLoop\Factory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

class Base extends Command
{
    public $memoryLimit = 128;
    public $timeout = 120;
    protected $loop;

    protected $outputHandler;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->loop = Factory::create();
    }

    public function runLoop()
    {
        $this->loop->run();
    }

    public function addReadStream($socket, Closure $closure)
    {
        $this->loop->addReadStream($socket, $closure);

        return $this;
    }

    public function addPeriodicTimer($interval, Closure $closure)
    {
        $this->loop->addPeriodicTimer($interval, $closure);

        return $this;
    }

    public function listen($topic, $channel = null)
    {
        $process = $this->makeProcess($topic, $channel);

        while (true) {
            $this->runProcess($process);
        }
    }

    public function makeProcess($topic, $channel)
    {
        $command = "{$this->phpBinary()} {$this->consoleBinary()} {$this->getName()} {$topic} {$channel}";
        $command = ProcessUtils::validateInput(null, $command);

        return new Process($command, null, null, null, null);
    }

    public function runProcess(Process $process)
    {
        $process->run(function ($type, $line) {
            $this->handleWorkerOutput($type, $line);
        });

        if ($this->memoryExceeded()) {
            die;
        }
    }

    public function memoryExceeded()
    {
        return (memory_get_usage() / 1024 / 1024) >= $this->memoryLimit;
    }

    protected function handleWorkerOutput($type, $line)
    {
        if (isset($this->outputHandler)) {
            call_user_func($this->outputHandler, $type, $line);
        }
    }

    protected function setOutputHandler(Closure $outputHandler)
    {
        $this->outputHandler = $outputHandler;

        return $this;
    }

    protected function phpBinary()
    {
        if (false === $binary = (new PhpExecutableFinder())->find(false)) {
            throw new RuntimeException('Unable to find the PHP binary.');
        }

        return $binary;
    }

    protected function consoleBinary()
    {
        return defined('CONSOLE_BINARY')
            ? ProcessUtils::validateInput(null, CONSOLE_BINARY)
            : 'bin/console';
    }
}
