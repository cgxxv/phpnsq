# phpnsq

[![Build Status](https://travis-ci.org/okstuff/phpnsq.svg?branch=master)](https://travis-ci.org/okstuff/phpnsq)

## Have a try
```shell
composer install
php bin/console phpnsq:sub <topic> <channel>
php bin/console phpnsq:pub <topic>
php bin/console phpnsq:mpub <topic>
php bin/console phpnsq:dpub <topic> <defer_time>
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
<?php

require __DIR__ . "/../vendor/autoload.php";

use OkStuff\PhpNsq\Command\Subscribe;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\PhpNsq;

$config = require __DIR__ . '/../src/config/phpnsq.php';
$phpnsq = new PhpNsq($config);

//TODO: to be done

```
