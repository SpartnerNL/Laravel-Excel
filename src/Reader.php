<?php

namespace Maatwebsite\Excel;

interface Reader
{
    /**
     * Return all sheets/rows.
     *
     * @param array $columns
     *
     * @return LaravelExcelReader
     */
    public function all($columns = []);

    /**
     * Get first row/sheet only.
     *
     * @param array $columns
     *
     * @return SheetCollection|RowCollection
     */
    public function first($columns = []);

    /**
     * Get all sheets/rows.
     *
     * @param array $columns
     *
     * @return SheetCollection|RowCollection
     */
    public function get($columns = []);
}
