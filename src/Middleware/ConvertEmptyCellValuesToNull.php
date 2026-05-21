<?php

namespace Maatwebsite\Excel\Middleware;

class ConvertEmptyCellValuesToNull extends CellMiddleware
{
    public function __invoke(mixed $value, callable $next): mixed
    {
        return $next(
            $value === '' ? null : $value
        );
    }
}
