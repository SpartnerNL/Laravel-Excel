<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
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
     * @var Sheet
     */
    protected $sheet;

    /**
     * @param Sheet                  $sheet
     * @param ColumnIteratorDelegate $iterator
     * @param Configuration          $configuration
     */
    public function __construct(Sheet $sheet, ColumnIteratorDelegate $iterator, Configuration $configuration)
    {
        $this->iterator      = $iterator;
        $this->configuration = $configuration;
        $this->sheet         = $sheet;
    }

    /**
     * Return the current element.
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return Column
     * @since 5.0.0
     */
    public function current()
    {
        return new Column($this->iterator->current(), $this->sheet, $this->configuration);
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
