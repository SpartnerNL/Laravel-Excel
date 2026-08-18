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
        private readonly ?int $headingRow = null,
    ) {
        // Subtract 1 row from the start row, so a limit of 1 row
        // will have the same start and end row.
        $this->endRow = ($this->startRow - 1) + $limit;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        //  Only read the heading row, and the rows within the limited range.
        return $row === $this->headingRow
            || ($row >= $this->startRow && $row <= $this->endRow);
    }
}
