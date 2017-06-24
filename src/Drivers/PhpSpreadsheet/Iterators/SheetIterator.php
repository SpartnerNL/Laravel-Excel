<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Iterator as WorksheetIterator;

class SheetIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var WorksheetIterator
     */
    protected $iterator;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param WorksheetIterator $iterator
     * @param Configuration     $configuration
     */
    public function __construct(WorksheetIterator $iterator, Configuration $configuration)
    {
        $this->iterator      = $iterator;
        $this->configuration = $configuration;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     *
     * @return Sheet
     *
     * @since 5.0.0
     */
    public function current()
    {
        return new Sheet($this->iterator->current(), $this->configuration);
    }

    /**
     * @return Sheet
     */
    public function first(): Sheet
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
