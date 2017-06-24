<?php

namespace Maatwebsite\Excel;

use Iterator;
use Countable;
use IteratorAggregate;

interface Row extends IteratorAggregate, Countable
{
    /**
     * @param string $column
     *
     * @return \Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row
     */
    public function setStartColumn(string $column);

    /**
     * @param string $column
     *
     * @return \Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row
     */
    public function setEndColumn(string $column);

    /**
     * @param string $column
     * @param bool   $createIfNotExist
     *
     * @return Cell
     */
    public function cell(string $column, bool $createIfNotExist = false): Cell;

    /**
     * @param string|null $startColumn
     * @param string|null $endColumn
     *
     * @return Iterator|Cell[]
     */
    public function cells(string $startColumn = 'A', string $endColumn = null);

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return int
     */
    public function getRowNumber(): int;

    /**
     * @return string
     */
    public function getHighestColumn(): string;

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return Iterator
     */
    public function getCellIterator(string $startColumn = 'A', string $endColumn = null);
}
