<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator as ColumnIteratorDelegate;

class ColumnIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var ColumnIteratorDelegate
     */
    protected $iterator;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param ColumnIteratorDelegate $iterator
     * @param Configuration       $configuration
     */
    public function __construct(ColumnIteratorDelegate $iterator, Configuration $configuration)
    {
        $this->iterator      = $iterator;
        $this->configuration = $configuration;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     *
     * @return Column
     *
     * @since 5.0.0
     */
    public function current()
    {
        return new Column($this->iterator->current(), $this->configuration);
    }

    /**
     * @return Column
     */
    public function first(): Column
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
