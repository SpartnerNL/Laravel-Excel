<?php

namespace Maatwebsite\Excel;

interface Reader
{
    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return Reader
     */
    public function load(string $filePath, callable $callback = null): Reader;
}
