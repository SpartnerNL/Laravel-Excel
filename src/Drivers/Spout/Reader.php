<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Spreadsheet;
use Maatwebsite\Excel\Reader as ReaderInterface;

class Reader implements ReaderInterface
{
    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return Spreadsheet
     */
    public function load(string $filePath, callable $callback = null): Spreadsheet
    {
        if (is_callable($callback)) {
            $callback($this);
        }

        //return $this;
    }
}
