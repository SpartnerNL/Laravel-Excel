<?php

namespace Maatwebsite\Excel\Concerns;

interface WithStartRow
{
    /**
     * @return int
     */
    public function startRow(): int;
}
