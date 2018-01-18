<?php

require __DIR__ . "/../vendor/autoload.php";

use OkStuff\PhpNsq\Command\Subscribe;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\PhpNsq;

$config = require __DIR__ . '/../src/config/phpnsq.php';
$phpnsq = new PhpNsq($config);

//TODO: to be done

