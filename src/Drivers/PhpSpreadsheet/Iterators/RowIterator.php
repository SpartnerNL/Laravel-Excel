<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Iterator as WorksheetIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator as RowIteratorDelegate;

class RowIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var RowIteratorDelegate
     */
    private $iterator;

    /**
     * @param RowIteratorDelegate $iterator
     */
    public function __construct(RowIteratorDelegate $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return new Row($this->iterator->current());
    }

    /**
     * @return Row
     */
    public function first(): Row
    {
        $this->rewind();

        return $this->current();
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }
}
