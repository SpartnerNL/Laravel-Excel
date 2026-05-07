<?php

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter
{
    /**
     * @var int
     */
    private $endRow;

    /**
     * @param  int  $headingRow
     * @param  int  $startRow
     * @param  int  $chunkSize
     * @param  string  $worksheetName
     */
    public function __construct(private int $headingRow, private int $startRow, int $chunkSize, private string $worksheetName)
    {
        $this->endRow        = $this->startRow + $chunkSize;
    }

    /**
     * @param  string  $column
     * @param  int  $row
     * @param  string  $worksheetName
     * @return bool
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
        return ($worksheetName === $this->worksheetName || $worksheetName === '')
            && ($row === $this->headingRow || ($row >= $this->startRow && $row < $this->endRow));
    }
}
