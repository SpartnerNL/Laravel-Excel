<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\CellIterator;
use PhpOffice\PhpSpreadsheet\Cell as PhpSpreadsheetCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as PhpSpreadsheetRow;
use Traversable;

class Row implements IteratorAggregate, Countable
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
     * @param PhpSpreadsheetRow $row
     */
    public function __construct(PhpSpreadsheetRow $row)
    {
        $this->row = $row;

        $this->setStartColumn('A');
        $this->setEndColumn($this->getHighestColumn());
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
     * @param string|null $startColumn
     * @param string|null $endColumn
     *
     * @return Cell[]|CellIterator
     */
    public function cells(string $startColumn = null, string $endColumn = null): CellIterator
    {
        if ($startColumn !== null) {
            $this->setStartColumn($startColumn);
        }

        if ($endColumn !== null) {
            $this->setEndColumn($endColumn);
        }

        return $this->getIterator();
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
        return new CellIterator(
            $this->row->getCellIterator($this->startColumn, $this->endColumn)
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

        return PhpSpreadsheetCell::columnIndexFromString($highestColumn);
    }
}
