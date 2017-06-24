<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use Traversable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Row as RowInterface;
use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as PhpSpreadsheetRow;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;

class Row implements RowInterface, IteratorAggregate, Countable
{
    /**
     * @var PhpSpreadsheetRow
     */
    protected $row;

    /**
     * @var string
     */
    protected $startColumn = 'A';

    /**
     * @var string
     */
    protected $endColumn;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Sheet
     */
    protected $sheet;

    /**
     * @param PhpSpreadsheetRow $row
     * @param Sheet             $sheet
     * @param Configuration     $configuration
     */
    public function __construct(PhpSpreadsheetRow $row, Sheet $sheet, Configuration $configuration)
    {
        $this->row           = $row;
        $this->sheet         = $sheet;
        $this->configuration = $configuration;
    }

    /**
     * @param string $column
     *
     * @return Row
     */
    public function setStartColumn(string $column)
    {
        $this->startColumn = $column;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return Row
     */
    public function setEndColumn(string $column)
    {
        $this->endColumn = $column;

        return $this;
    }

    /**
     * @param string $column
     * @param bool   $createIfNotExist
     *
     * @return Cell
     */
    public function cell(string $column, bool $createIfNotExist = false)
    {
        return $this->sheet->cell(
            $column . $this->getRowNumber(),
            $createIfNotExist
        );
    }

    /**
     * @param string|null $startColumn
     * @param string|null $endColumn
     *
     * @return CellIterator|Cell[]
     */
    public function cells(string $startColumn = 'A', string $endColumn = null)
    {
        return $this->getCellIterator($startColumn, $endColumn);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $cells = [];
        foreach ($this->getIterator() as $cell) {
            $cells[] = (string) $cell;
        }

        return $cells;
    }

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->row->getRowIndex();
    }

    /**
     * @return string
     */
    public function getHighestColumn(): string
    {
        return $this->row->getWorksheet()->getHighestColumn(
            $this->getRowNumber()
        );
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
        return $this->getCellIterator($this->startColumn, $this->endColumn);
    }

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return CellIterator
     */
    public function getCellIterator(string $startColumn = 'A', string $endColumn = null): CellIterator
    {
        if ($endColumn === null) {
            $endColumn = $this->getHighestColumn();
        }

        return new CellIterator(
            $this->row->getCellIterator($startColumn, $endColumn),
            $this->configuration
        );
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
        $highestColumn = $this->getHighestColumn();

        return $this->getColumnIndex($highestColumn);
    }

    /**
     * @param string $column
     *
     * @return int
     */
    protected function getColumnIndex(string $column): int
    {
        return PhpSpreadsheetCell::columnIndexFromString($column);
    }
}
