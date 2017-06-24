<?php

namespace Maatwebsite\Excel;

use Iterator;
use IteratorAggregate;
use Traversable;

interface Column extends IteratorAggregate
{
    /**
     * @return string
     */
    public function getColumnIndex(): string;

    /**
     * @param int $rowNumber
     *
     * @return Column
     */
    public function setStartRow(int $rowNumber = 1): Column;

    /**
     * @param int|null $rowNumber
     *
     * @return Column
     */
    public function setEndRow(int $rowNumber = null): Column;

    /**
     * @param int  $row
     * @param bool $createIfNotExist
     *
     * @return Cell
     */
    public function cell(int $row, bool $createIfNotExist = false): Cell;

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Iterator|Cell[]
     */
    public function cells(int $startRow = 1, int $endRow = null);

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|Cell[]
     * @since 5.0.0
     */
    public function getIterator();

    /**
     * @param int      $startRow
     * @param int|null $endRow
     *
     * @return Iterator|Cell[]
     */
    public function getCellIterator(int $startRow = 1, int $endRow = null);
}
