<?php

namespace OkStuff\PhpNsq\Command;

use OkStuff\PhpNsq\PhpNsq;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishMulti extends Base
{
    CONST COMMAND_NAME = "phpnsq:mpub";

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument("topic", InputArgument::REQUIRED, "The topic you want to notify")
            ->setDescription('publish new notification.')
            ->setHelp("This command allows you to publish notifications...");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $topic  = $input->getArgument("topic");
        $phpnsq = self::$phpnsq;
        $this->addPeriodicTimer(5, function () use ($phpnsq, $topic) {
            $time   = date("Y-m-d H:i:s");
            $bodies = [];
            for ($i = 0; $i < 10; $i++) {
                $bodies[] = "Published `$time` to `$topic`" . (5 * ($i + 1)) . " <=> " . str_repeat("a", 5 * ($i + 1));
            }
            $phpnsq->setTopic($topic)->multiPublish(...$bodies);

            $memory    = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3) . 'K';
            dump("############ Current memory usage: {$formatted} ############");
        });
        $this->runLoop();
    }
}
