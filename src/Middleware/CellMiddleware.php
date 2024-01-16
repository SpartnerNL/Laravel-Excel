<?php

namespace Maatwebsite\Excel\Middleware;

use Maatwebsite\Excel\Cell;

abstract class CellMiddleware
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function __invoke($value, callable $next);
}
