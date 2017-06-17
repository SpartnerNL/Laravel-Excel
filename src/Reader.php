<?php

namespace Maatwebsite\Excel;

interface Reader
{
    /**
     * @param string        $filepath
     * @param callable|null $callback
     *
     * @return Reader
     */
    public function load(string $filepath, callable $callback = null): Reader;
}
