<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Reader as ReaderInterface;
use Maatwebsite\Excel\Spreadsheet;

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
