<?php

namespace Seoperin\LaravelExcel\Concerns;

interface WithLimit
{
    /**
     * @return int
     */
    public function limit(): int;
}
