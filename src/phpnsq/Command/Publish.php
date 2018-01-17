<?php

namespace OkStuff\PhpNsq\Command;

use OkStuff\PhpNsq\Message\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Publish extends Base
{
    CONST COMMAND_NAME = "phpnsq:pub";

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument("topic", InputArgument::REQUIRED, "The topic you want to notify")
            ->setDescription('publish new notification.')
            ->setHelp("This command allows you to publish notifications...");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $topic   = $input->getArgument("topic");
        $phpnsq  = self::$phpnsq;
        $message = new Message();
        $this->addPeriodicTimer(5, function () use ($phpnsq, $topic, $message) {
            $time = date("Y-m-d H:i:s");
            $message->setBody("Published `$time` to `$topic`");
            $phpnsq->setTopic($topic)->publish($message);

            $memory    = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3) . 'K';
            dump("############ Current memory usage: {$formatted} ############");
        });
        $this->runLoop();
    }
}
