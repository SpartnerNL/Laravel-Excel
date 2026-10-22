<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithLimit;

class EndRowFinder
{
    public static function find(?Import $import, ?int $startRow = null, ?int $highestRow = null): ?int
    {
        if (!$import instanceof WithLimit) {
            return null;
        }

        // When no start row given,
        // use the first row as start row.
        $startRow ??= 1;

        // Subtract 1 row from the start row, so a limit
        // of 1 row, will have the same start and end row.
        $endRow = ($startRow - 1) + $import->limit();

        // A limit reaching past the highest row reads up to the highest row.
        if ($endRow > $highestRow) {
            return null;
        }

        return $endRow;
    }
}
