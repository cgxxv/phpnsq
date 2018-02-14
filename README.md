# phpnsq

[![Build Status](https://travis-ci.org/okstuff/phpnsq.svg?branch=master)](https://travis-ci.org/okstuff/phpnsq)

## Subscribe one topic and channel
```shell
php bin/console phpnsq:sub <topic> <channel>
```

## Install
```shell
composer require okstuff/phpnsq
```

## Notice
Before try this, you should install [nsq](http://nsq.io) by yourself.

## Examples

1. Publish

[embedmd]:# (examples/publish.php php)
```php
<?php

require __DIR__ . "/../vendor/autoload.php";

use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\PhpNsq;

$config = require __DIR__ . '/../src/config/phpnsq.php';
$phpnsq = new PhpNsq($config);

//normal publish
$message = new Message();
$message->setBody("Hello nsq.");
$phpnsq->setTopic("sample_topic")->publish(json_encode($message));

//defered publish
$message = [
    "title"   => "hello",
    "content" => "this is a nsq php client.",
];
$phpnsq->setTopic("sample_topic")->publishDefer(json_encode($message), 10);

//multiple publish
$messages = [
    "Hello, I am nsq client",
    "There are so many libraries developed by PHP",
    "Oh, no, PHP is not so good and slowly",
];
$phpnsq->setTopic("sample_topic")->publishMulti(...$messages);
```

2. Subscribe

[embedmd]:# (examples/subscribe.php php)
```php
#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\Command\Base;
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
                $phpnsq->getLogger()->info("READ", $message);
            });
        $this->addPeriodicTimer(5, function () use ($output) {
            $memory    = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3) . 'K';
            $output->writeln("############ Current memory usage: {$formatted} ############");
        });
        $this->runLoop();
    }
}

$application = new Application();

$config = require __DIR__.'/../src/config/phpnsq.php';

$application->add(new Subscribe($config));

$application->run();
```

3. Run in the terminal

```shell
php examples/subscribe.php phpnsq:sub sample_topic sample_channel
```