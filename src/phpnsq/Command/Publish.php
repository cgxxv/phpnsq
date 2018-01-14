<?php

namespace OkStuff\PhpNsq\Command;

use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\PhpNsq;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Publish extends Base
{
    CONST COMMAND_NAME = "phpnsq:publish";

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument("topic", InputArgument::REQUIRED, "The topic you want to notify")
            ->setDescription('publish new notification.')
            ->setHelp("This command allows you to publish notifications...");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = require_once __DIR__.'/../../config/phpnsq.php';
        $phpnsq = new PhpNsq($config);
        $body = [
            "title" => "Hello",
            "content" => "Welcome to php nsq client!"
        ];
        $message = new Message();
        $message->setId(123)->setBody(json_encode($body));
        $phpnsq->setTopic($input->getArgument("topic"))
            ->publish($message);
    }
}
