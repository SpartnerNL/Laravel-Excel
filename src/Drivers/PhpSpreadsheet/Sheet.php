<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use Traversable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Row as RowInterface;
use Maatwebsite\Excel\Cell as CellInterface;
use Maatwebsite\Excel\Sheet as SheetInterface;
use Maatwebsite\Excel\Column as ColumnInterface;
use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\RowIterator;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\ColumnIterator;

class Sheet implements SheetInterface
{
    /**
     * @var Worksheet
     */
    protected $worksheet;

    /**
     * @var int
     */
    protected $startRow = 1;

    /**
     * @var int|null
     */
    protected $endRow = null;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Worksheet     $worksheet
     * @param Configuration $configuration
     */
    public function __construct(Worksheet $worksheet, Configuration $configuration)
    {
        $this->worksheet     = $worksheet;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->worksheet->getTitle();
    }

    /**
     * @return int
     */
    public function getSheetIndex(): int
    {
        return $this->worksheet->getParent()->getIndex($this->worksheet);
    }

    /**
     * @param int $rowNumber
     *
     * @return SheetInterface
     */
    public function setStartRow(int $rowNumber = 1): SheetInterface
    {
        $this->startRow = $rowNumber;

        return $this;
    }

    /**
     * @param int|null $rowNumber
     *
     * @return SheetInterface
     */
    public function setEndRow(int $rowNumber = null): SheetInterface
    {
        $this->endRow = $rowNumber;

        return $this;
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Traversable|RowIterator|RowInterface[]
     */
    public function rows(int $startRow = 1, int $endRow = null)
    {
        return $this->getRowIterator($startRow, $endRow);
    }

    /**
     * @param int $rowNumber
     *
     * @return RowInterface
     */
    public function row(int $rowNumber): RowInterface
    {
        return $this->getRowIterator($rowNumber, $rowNumber + 1)->first();
    }

    /**
     * @return RowInterface
     */
    public function first(): RowInterface
    {
        return $this->row(1);
    }

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return Column[]|ColumnIterator
     */
    public function columns(string $startColumn = 'A', string $endColumn = null): ColumnIterator
    {
        return $this->getColumnIterator($startColumn, $endColumn);
    }

    /**
     * @param string $column
     *
     * @return ColumnInterface
     */
    public function column(string $column): ColumnInterface
    {
        $iterator = $this->getColumnIterator($column, ++$column);

        return $iterator->first();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $rows = [];
        foreach ($this->getIterator() as $row) {
            $rows[] = $row->toArray();
        }

        return $rows;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|RowIterator
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->getRowIterator($this->startRow, $this->endRow);
    }

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return IteratorAggregate|RowIterator
     */
    public function getRowIterator(int $startRow = 1, int $endRow = null)
    {
        return new RowIterator(
            $this,
            $this->worksheet->getRowIterator($startRow, $endRow),
            $this->configuration
        );
    }

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return IteratorAggregate|ColumnIterator
     */
    public function getColumnIterator(string $startColumn = 'A', string $endColumn = null)
    {
        return new ColumnIterator(
            $this,
            $this->worksheet->getColumnIterator($startColumn, $endColumn),
            $this->configuration
        );
    }

    /**
     * @param string $coordinate
     *
     * @return bool
     */
    public function hasCell(string $coordinate): bool
    {
        return $this->worksheet->cellExists($coordinate);
    }

    /**
     * @param string $coordinate
     * @param bool   $createIfNotExist
     *
     * @return CellInterface
     */
    public function cell(string $coordinate, bool $createIfNotExist = false): CellInterface
    {
        $cell = $this->worksheet->getCell($coordinate, $createIfNotExist);

        return new Cell($cell, $this->configuration);
    }

    /**
     * Count elements of an object.
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->worksheet->getHighestRow();
    }

    /**
     * @return string
     */
    public function getHighestColumn(): string
    {
        return $this->worksheet->getHighestColumn();
    }

    /**
     * @return int
     */
    public function columnCount(): int
    {
        return PhpSpreadsheetCell::columnIndexFromString($this->getHighestColumn());
    }
}
