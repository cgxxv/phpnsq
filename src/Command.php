<?php

namespace OkStuff\PHPNSQ;

class Command
{
    public $name;
    public $params;
    public $body;

    public function __construct(string $name, array $params, string $body)
    {
        $this->name = $name ?? 'AUTH';
        $this->params = $params;
        $this->body = $body;
    }
}

