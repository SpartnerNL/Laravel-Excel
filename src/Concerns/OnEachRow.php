<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Row;

interface OnEachRow
{
    /**
     * @return mixed
     */
    public function onRow(Row $row);
}
