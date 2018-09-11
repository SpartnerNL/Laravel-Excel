<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

interface OnEachRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row);
}
