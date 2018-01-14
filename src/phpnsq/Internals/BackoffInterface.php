<?php

namespace OkStuff\PhpNsq\Internals;

interface BackoffStrategy
{
    public function Calculate(int $attempt): int;
}
