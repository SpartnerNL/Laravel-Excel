<?php

namespace Maatwebsite\Excel\Middleware;

abstract class CellMiddleware
{
    abstract public function __invoke(mixed $value, callable $next): mixed;
}
