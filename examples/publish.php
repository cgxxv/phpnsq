<?php
require __DIR__ . "/../vendor/autoload.php";

use OkStuff\PhpNsq\PhpNsq;

$config = require __DIR__ . '/../src/config/phpnsq.php';
$phpnsq = new PhpNsq($config);

//normal publish
$msg = $phpnsq->setTopic("sample_topic")->publish("Hello nsq.");
var_dump($msg);

//defered publish
$msg = $phpnsq->setTopic("sample_topic")->publishDefer("this is a defered message.", 10);
var_dump($msg);

//multiple publish
$messages = [
    "Hello, I am nsq client",
    "There are so many libraries developed by PHP",
    "Oh, no, PHP is not so good and slowly",
];
$msg = $phpnsq->setTopic("sample_topic")->publishMulti(...$messages);
var_dump($msg);
