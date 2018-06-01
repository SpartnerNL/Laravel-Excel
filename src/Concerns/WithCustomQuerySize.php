<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomQuerySize
{
    /**
     * @return int
     */
    public function querySize(): int;
}
