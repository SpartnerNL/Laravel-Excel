<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use IteratorAggregate;
use Maatwebsite\Excel\Cell as CellInterface;
use Maatwebsite\Excel\Column as ColumnInterface;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Column as PhpSpreadsheetColumn;
use Traversable;

class Column implements ColumnInterface, IteratorAggregate
{
    /**
     * @var PhpSpreadsheetColumn
     */
    private $column;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @param PhpSpreadsheetColumn $column
     * @param Configuration        $configuration
     */
    public function __construct(PhpSpreadsheetColumn $column, Configuration $configuration)
    {
        $this->column        = $column;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getColumnIndex()
    {
        return $this->column->getColumnIndex();
    }

    /**
     * @param int $rowNumber
     *
     * @return ColumnInterface
     */
    public function setStartRow(int $rowNumber = 1): ColumnInterface
    {
        $this->startRow = $rowNumber;

        return $this;
    }

    /**
     * @param int|null $rowNumber
     *
     * @return ColumnInterface
     */
    public function setEndRow(int $rowNumber = null): ColumnInterface
    {
        $this->endRow = $rowNumber;

        return $this;
    }

    /**
     * @param int|null $startRow
     * @param int|null $endRow
     *
     * @return ColumnInterface|CellInterface[]
     */
    public function cells(int $startRow = null, int $endRow = null): ColumnInterface
    {
        if ($startRow !== null) {
            $this->setStartRow($startRow);
        }

        if ($endRow !== null) {
            $this->setEndRow($endRow);
        }

        return $this;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|CellIterator
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new CellIterator(
            $this->column->getCellIterator($this->startRow, $this->endRow),
            $this->configuration
        );
    }
}