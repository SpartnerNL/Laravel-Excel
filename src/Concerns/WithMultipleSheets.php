<?php

namespace Maatwebsite\Excel\Concerns;

interface WithMultipleSheets
{
    /**
     * @return iterable
     */
    public function sheets(): iterable;
}
