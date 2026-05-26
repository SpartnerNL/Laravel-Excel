<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter
{
    private readonly int $endRow;

    public function __construct(
        private readonly int $headingRow,
        private readonly int $startRow,
        int $chunkSize,
        private readonly string $worksheetName,
    ) {
        $this->endRow = $this->startRow + $chunkSize;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
        return ($worksheetName === $this->worksheetName || $worksheetName === '')
            && ($row === $this->headingRow || ($row >= $this->startRow && $row < $this->endRow));
    }
}
