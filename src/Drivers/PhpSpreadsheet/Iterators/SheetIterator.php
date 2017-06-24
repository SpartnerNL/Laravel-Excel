<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Iterator as WorksheetIterator;

class SheetIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var WorksheetIterator
     */
    private $iterator;

    /**
     * @param WorksheetIterator $iterator
     */
    public function __construct(WorksheetIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     *
     * @since 5.0.0
     */
    public function current()
    {
        return new Sheet($this->iterator->current());
    }

    /**
     * @return Sheet
     */
    public function first(): Sheet
    {
        $this->rewind();

        $this->next();

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
