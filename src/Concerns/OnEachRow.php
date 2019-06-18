<?php

namespace Seoperin\LaravelExcel\Concerns;

use Seoperin\LaravelExcel\Row;

interface OnEachRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row);
}
