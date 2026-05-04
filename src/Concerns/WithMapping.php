<?php

namespace Maatwebsite\Excel\Concerns;

/**
 * @template RowType of mixed
 */
interface WithMapping
{
    /**
     * @param  RowType  $row
     */
    public function map($row): array;
}
