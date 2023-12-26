<?php

namespace Maatwebsite\Excel\Concerns;

interface WithLimit
{
    /**
     * @return int
     */
    public function limit(): int;
}
