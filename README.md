# phpnsq

[![Build Status](https://travis-ci.org/okstuff/phpnsq.svg?branch=master)](https://travis-ci.org/okstuff/phpnsq)

## Install
```shell
composer require okstuff/phpnsq
```

## Subscribe one topic and channel
```shell
php examples/console phpnsq:sub <topic> <channel>
```

## Notice
Before try this, you should install [nsq](http://nsq.io) by yourself.

## Examples

1. Publish

[embedmd]:# (examples/publish.php php)
```php
<?php
require __DIR__ . "/../vendor/autoload.php";

use OkStuff\PhpNsq\PhpNsq;

$config = require __DIR__ . '/../src/config/phpnsq.php';
$phpnsq = new PhpNsq($config);

if ($config["nsq"]["auth_switch"]) {
    $phpnsq->auth($config["nsq"]["auth_secret"]);
}

//normal publish
$phpnsq->setTopic("test")->publish("Hello nsq.");

//defered publish
$phpnsq->setTopic("sample_topic")->publishDefer("this is a defered message.", 10);

//multiple publish
$messages = [
    "Hello, I am nsq client",
    "There are so many libraries developed by PHP",
    "Oh, no, PHP is not so good and slowly",
];
$phpnsq->setTopic("sample_topic")->publishMulti(...$messages);
```

2. Subscribe

[embedmd]:# (src/phpnsq/Cmd/Subscribe.php php)
```php
<?php

namespace OkStuff\PhpNsq\Cmd;

use OkStuff\PhpNsq\Stream\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Subscribe extends Base
{
    CONST COMMAND_NAME = 'phpnsq:sub';

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument("topic", InputArgument::REQUIRED, "The topic you want to subscribe")
            ->addArgument("channel", InputArgument::REQUIRED, "The channel you want to subscribe")
            ->setDescription('subscribe new notification.')
            ->setHelp("This command allows you to subscribe notifications...");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $phpnsq = self::$phpnsq;
        $phpnsq->setTopic($input->getArgument("topic"))
            ->setChannel($input->getArgument("channel"))
            ->subscribe($this, function (Message $message) use ($phpnsq, $output) {
                // $output->writeln($message->toJson());
                $phpnsq->getLogger()->info("READ", $message->toArray());
            });
        //excuted every five seconds.
        $this->addPeriodicTimer(5, function () use ($output) {
            $memory    = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3) . 'K';
            $output->writeln(date("Y-m-d H:i:s") . " ############ Current memory usage: {$formatted} ############");
        });
        $this->runLoop();
    }
}
```

3. Console

[embedmd]:# (examples/console php)
```php
#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use OkStuff\PhpNsq\Cmd\Subscribe;

$application = new Application();

$config = require __DIR__.'/../src/config/phpnsq.php';

$application->add(new Subscribe($config, null, $config["nsq"]["auth_switch"]));

$application->run();
```
