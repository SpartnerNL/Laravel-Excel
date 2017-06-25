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
     * @var array
     */
    protected $headings = [];

    /**
     * @param Sheet               $sheet
     * @param array               $headings
     * @param RowIteratorDelegate $iterator
     * @param Configuration       $configuration
     */
    public function __construct(
        Sheet $sheet,
        array $headings,
        RowIteratorDelegate $iterator,
        Configuration $configuration
    ) {
        $this->iterator      = $iterator;
        $this->configuration = $configuration;
        $this->sheet         = $sheet;
        $this->headings      = $headings;
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
        return new Row($this->iterator->current(), $this->headings, $this->sheet, $this->configuration);
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }
}
