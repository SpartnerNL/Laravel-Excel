<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Traversable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Cell as CellInterface;
use Maatwebsite\Excel\Column as ColumnInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Column as PhpSpreadsheetColumn;

class Column implements ColumnInterface, IteratorAggregate
{
    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @var PhpSpreadsheetColumn
     */
    private $column;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @param PhpSpreadsheetColumn $column
     * @param Sheet                $sheet
     * @param Configuration        $configuration
     */
    public function __construct(PhpSpreadsheetColumn $column, Sheet $sheet, Configuration $configuration)
    {
        $this->column        = $column;
        $this->configuration = $configuration;
        $this->sheet         = $sheet;
    }

    /**
     * @return string
     */
    public function getColumnIndex(): string
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
     * @param int  $row
     * @param bool $createIfNotExist
     *
     * @return Cell
     */
    public function cell(int $row, bool $createIfNotExist = false): Cell
    {
        return $this->sheet->cell(
            $this->getColumnIndex() . $row,
            $createIfNotExist
        );
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return CellIterator|CellInterface[]
     */
    public function cells(int $startRow = 1, int $endRow = null)
    {
        return $this->getCellIterator($startRow, $endRow);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $cells = [];

        foreach ($this->cells() as $cell) {
            $cells[] = (string) $cell;
        }

        return $cells;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|CellIterator|CellInterface[]
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->getCellIterator($this->startRow, $this->endRow);
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Traversable|CellIterator|CellInterface[]
     */
    public function getCellIterator(int $startRow = 1, int $endRow = null)
    {
        return new CellIterator(
            $this->column->getCellIterator($startRow, $endRow),
            $this->configuration
        );
    }
}
