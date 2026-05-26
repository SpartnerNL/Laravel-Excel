<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LimitFilter implements IReadFilter
{
    private readonly int $endRow;

    public function __construct(
        private readonly int $startRow,
        int $limit,
    ) {
        $this->endRow = $this->startRow + $limit;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
