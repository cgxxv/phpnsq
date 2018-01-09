<?php

namespace OkStuff\PHPNSQ\Internals;

interface BackoffStrategy
{
    public function Calculate(int $attempt): int;
}