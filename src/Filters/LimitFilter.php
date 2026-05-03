<?php

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LimitFilter implements IReadFilter
{
    /**
     * @var int
     */
    private $endRow;

    /**
     * @param  int  $startRow
     * @param  int  $limit
     */
    public function __construct(private int $startRow, int $limit)
    {
        $this->endRow   = $this->startRow + $limit;
    }

    /**
     * @param  string  $column
     * @param  int  $row
     * @param  string  $worksheetName
     * @return bool
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
