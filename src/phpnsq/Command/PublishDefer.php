<?php

namespace OkStuff\PhpNsq\Command;

use OkStuff\PhpNsq\Message\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishDefer extends Base
{
    CONST COMMAND_NAME = "phpnsq:dpub";

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument("topic", InputArgument::REQUIRED, "The topic you want to notify")
            ->addArgument("defer_time", InputArgument::REQUIRED, "The defer time you want to notify")
            ->setDescription('publish new notification.')
            ->setHelp("This command allows you to publish notifications...");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $topic     = $input->getArgument("topic");
        $deferTime = $input->getArgument("defer_time");
        $phpnsq    = self::$phpnsq;
        $message   = new Message();
        $this->addPeriodicTimer(5, function () use ($phpnsq, $topic, $deferTime, $message) {
            $time = date("Y-m-d H:i:s");
            $message->setBody("Published `$time` to `$topic`");
            $phpnsq->setTopic($topic)->publishDefer($message, $deferTime);

            $memory    = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3) . 'K';
            dump("############ Current memory usage: {$formatted} ############");
        });
        $this->runLoop();
    }
}
