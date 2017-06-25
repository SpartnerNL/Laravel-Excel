<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;

use IteratorAggregate;
use PhpOffice\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet;
use Maatwebsite\Excel\Column as ColumnInterface;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators\ColumnIterator;

trait SheetHasColumns
{
    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return ColumnInterface[]|ColumnIterator
     */
    public function columns(string $startColumn = 'A', string $endColumn = null)
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
     * @return string
     */
    public function getHighestColumn(): string
    {
        return $this->getWorksheet()->getHighestColumn();
    }

    /**
     * @return int
     */
    public function columnCount(): int
    {
        return Cell::columnIndexFromString($this->getHighestColumn());
    }

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return IteratorAggregate|ColumnIterator
     */
    public function getColumnIterator(string $startColumn = 'A', string $endColumn = null)
    {
        // TODO: add interface
        return new ColumnIterator(
            $this,
            $this->getWorksheet()->getColumnIterator($startColumn, $endColumn),
            $this->configuration
        );
    }

    /**
     * @return Worksheet
     */
    abstract public function getWorksheet(): Worksheet;
}
