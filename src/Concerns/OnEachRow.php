<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Row;

interface OnEachRow
{
    /**
     * @param  Row  $row
     */
    public function onRow(Row $row);
}
