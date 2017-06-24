<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as PhpSpreadsheetRow;
use Traversable;

class Row implements IteratorAggregate, Countable
{
    /**
     * @var PhpSpreadsheetRow
     */
    private $row;

    /**
     * @param PhpSpreadsheetRow $row
     */
    public function __construct(PhpSpreadsheetRow $row)
    {
        $this->row = $row;
    }

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->row->getRowIndex();
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new CellIterator(
            $this->row->getCellIterator()
        );
    }

    /**
     * Count elements of an object.
     *
     * @link  http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return $this->row->getWorksheet()->getHighestColumn(
            $this->getRowNumber()
        );
    }
}
