<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class HeadingRowImport implements WithLimit, WithMapping, WithStartRow
{
    use Importable;

    public function __construct(private int $headingRow = 1)
    {
    }

    public function startRow(): int
    {
        return $this->headingRow;
    }

    public function limit(): int
    {
        return 1;
    }

    /**
     * @param  mixed  $row
     */
    public function map($row): array
    {
        return HeadingRowFormatter::format($row);
    }
}
