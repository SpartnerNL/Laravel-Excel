<?php

namespace Maatwebsite\Excel;

use Countable;
use Iterator;
use IteratorAggregate;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;

interface Spreadsheet extends IteratorAggregate, Countable
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return Iterator|Sheet[]
     */
    public function sheets();

    /**
     * @param string|int    $nameOrIndex
     * @param callable|null $callback
     *
     * @return Sheet
     */
    public function sheet($nameOrIndex, callable $callback = null): Sheet;

    /**
     * @param string        $name
     * @param callable|null $callback
     *
     * @throws SheetNotFoundException
     * @return Sheet
     */
    public function sheetByName(string $name, callable $callback = null): Sheet;

    /**
     * @param int           $index
     * @param callable|null $callback
     *
     * @throws SheetNotFoundException
     * @return Sheet
     */
    public function sheetByIndex(int $index, callable $callback = null): Sheet;

    /**
     * @return Sheet
     */
    public function first(): Sheet;

    /**
     * @return Iterator|Sheet[]
     */
    public function getSheetIterator();
}
