<?php

namespace Maatwebsite\Excel\Middleware;

class TrimCellValue extends CellMiddleware
{
    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function __invoke($value, callable $next)
    {
        if (!is_string($value)) {
            return $next($value);
        }

        // Remove whitespace, BOM and zero width spaces.
        $cleaned = preg_replace('~^[\s\x{FEFF}\x{200B}]+|[\s\x{FEFF}\x{200B}]+$~u', '', $value) ?? trim($value);

        return $next($cleaned);
    }
}
