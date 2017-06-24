<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator as RowIteratorDelegate;

class RowIterator extends IteratorAdapter implements Iterator
{
    /**
     * @var RowIteratorDelegate
     */
    protected $iterator;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Sheet
     */
    protected $sheet;

    /**
     * @param Sheet               $sheet
     * @param RowIteratorDelegate $iterator
     * @param Configuration       $configuration
     */
    public function __construct(Sheet $sheet, RowIteratorDelegate $iterator, Configuration $configuration)
    {
        $this->iterator      = $iterator;
        $this->configuration = $configuration;
        $this->sheet         = $sheet;
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
        return new Row($this->iterator->current(), $this->sheet, $this->configuration);
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
