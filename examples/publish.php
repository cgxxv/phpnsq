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
