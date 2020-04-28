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
