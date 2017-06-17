<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Writer as WriterInterface;

class Writer implements WriterInterface
{
    /**
     * @param callable|null $callback
     *
     * @return WriterInterface
     */
    public function create(callable $callback = null): WriterInterface
    {
        if (is_callable($callback)) {
            $callback($this);
        }

        return $this;
    }
}