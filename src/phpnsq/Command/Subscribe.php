<?php

namespace OkStuff\PhpNsq\Command;

use OkStuff\PhpNsq\PhpNsq;
use OkStuff\PhpNsq\Message\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Subscribe extends Base
{
    CONST COMMAND_NAME = 'phpnsq:subscribe';

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
        $config = require_once __DIR__.'/../../config/phpnsq.php';
        $phpnsq = new PhpNsq($config);
        $self = $this;
        $phpnsq->setTopic($input->getArgument("topic"))
            ->setChannel($input->getArgument("channel"))
            ->subscribe($self, function(Message $message) use ($output) {
                $output->writeln("test........");
                $output->writeln("READ\t" . $message->getId() . "\t" . $message->getBody());
            });
        $this->addPeriodicTimer(1, function () use ($output) {
            $memory = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3).'K';
            $output->writeln("############ Current memory usage: {$formatted} ############");
        })->runLoop();

//        $this->setOutputHandler(function ($type, $line) use ($output) {
//                $output->writeln($line);
//            })
//            ->listen(
//                $input->getArgument("topic"),
//                $input->getArgument("channel")
//            );
    }
}
