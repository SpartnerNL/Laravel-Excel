<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Reader as ReaderInterface;

class Reader implements ReaderInterface
{
    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return ReaderInterface
     */
    public function load(string $filePath, callable $callback = null): ReaderInterface
    {
        if (is_callable($callback)) {
            $callback($this);
        }

        return $this;
    }
}
