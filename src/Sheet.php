<?php

namespace Maatwebsite\Excel;

use Iterator;
use Countable;
use IteratorAggregate;

interface Sheet extends IteratorAggregate, Countable
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return int
     */
    public function getSheetIndex(): int;

    /**
     * @param int $rowNumber
     *
     * @return Sheet
     */
    public function setStartRow(int $rowNumber = 1): Sheet;

    /**
     * @param int|null $rowNumber
     *
     * @return Sheet
     */
    public function setEndRow(int $rowNumber = null): Sheet;

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Iterator|Row[]
     */
    public function rows(int $startRow = 1, int $endRow = null);

    /**
     * @param int $rowNumber
     *
     * @return Row
     */
    public function row(int $rowNumber): Row;

    /**
     * @return Row
     */
    public function first(): Row;

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return Column[]
     */
    public function columns(string $startColumn = 'A', string $endColumn = null);

    /**
     * @param string $column
     *
     * @return Column
     */
    public function column(string $column): Column;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Iterator
     */
    public function getRowIterator(int $startRow = 1, int $endRow = null);

    /**
     * @param string      $startColumn
     * @param string|null $endColumn
     *
     * @return Iterator
     */
    public function getColumnIterator(string $startColumn = 'A', string $endColumn = null);

    /**
     * @param string $coordinate
     *
     * @return bool
     */
    public function hasCell(string $coordinate): bool;

    /**
     * @param string $coordinate
     * @param bool   $createIfNotExist
     *
     * @return Cell
     */
    public function cell(string $coordinate, bool $createIfNotExist = false): Cell;

    /**
     * @return string
     */
    public function getHighestColumn(): string;

    /**
     * @return int
     */
    public function columnCount(): int;
}
