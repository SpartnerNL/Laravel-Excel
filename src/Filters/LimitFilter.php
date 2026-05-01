<?php

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LimitFilter implements IReadFilter
{
    /**
     * @var int
     */
    private $startRow;

    /**
     * @var int
     */
    private $endRow;

    public function __construct(int $startRow, int $limit)
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $limit;
    }

    public function readCell(string $column, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
