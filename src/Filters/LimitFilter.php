<?php

namespace Maatwebsite\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * phpspreadsheet 2.0 added a native `: bool` return type to IReadFilter::readCell().
 * Two class definitions are needed to satisfy both interface versions without
 * forcing a breaking signature change on downstream subclasses.
 *
 * @see https://github.com/PHPOffice/PhpSpreadsheet/releases/tag/2.0.0
 */
trait LimitFilterBase
{
    /**
     * @var int
     */
    private $startRow;

    /**
     * @var int
     */
    private $endRow;

    /**
     * @param  int  $startRow
     * @param  int  $limit
     */
    public function __construct(int $startRow, int $limit)
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $limit;
    }
}

if ((new \ReflectionMethod(IReadFilter::class, 'readCell'))->hasReturnType()) {
    class LimitFilter implements IReadFilter
    {
        use LimitFilterBase;

        /**
         * @param  string  $column
         * @param  int  $row
         * @param  string  $worksheetName
         */
        public function readCell($column, $row, $worksheetName = ''): bool
        {
            return $row >= $this->startRow && $row <= $this->endRow;
        }
    }
} else {
    class LimitFilter implements IReadFilter
    {
        use LimitFilterBase;

        /**
         * @param  string  $column
         * @param  int  $row
         * @param  string  $worksheetName
         * @return bool
         */
        public function readCell($column, $row, $worksheetName = '')
        {
            return $row >= $this->startRow && $row <= $this->endRow;
        }
    }
}
