<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Columns\Column;

interface WithColumns
{
    /**
     * Keys are optional. When given, they are either a column letter (`B`) or,
     * in combination with WithHeadingRow, the heading of the column.
     *
     * @return array<array-key, Column|list<Column>>
     */
    public function columns(): array;
}
