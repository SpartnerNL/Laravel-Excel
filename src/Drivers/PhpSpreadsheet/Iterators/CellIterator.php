<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator as CellIteratorDelegate;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnCellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;

class CellIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var CellIteratorDelegate|RowCellIterator|ColumnCellIterator
     */
    private $iterator;

    /**
     * @param CellIteratorDelegate $iterator
     */
    public function __construct(CellIteratorDelegate $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     *
     * @return Cell
     *
     * @since 5.0.0
     */
    public function current()
    {
        return new Cell($this->getIterator()->current());
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }
}
