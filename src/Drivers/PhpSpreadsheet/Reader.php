<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Reader as ReaderInterface;

class Reader implements ReaderInterface
{
    /**
     * @param string        $filepath
     * @param callable|null $callback
     *
     * @return ReaderInterface
     */
    public function load(string $filepath, callable $callback = null): ReaderInterface
    {
        if (is_callable($callback)) {
            $callback($this);
        }

        return $this;
    }
}