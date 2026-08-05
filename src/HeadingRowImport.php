<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

/**
 * @implements WithMapping<array<array-key, mixed>>
 */
class HeadingRowImport implements Import, WithLimit, WithMapping, WithStartRow
{
    use Importable;

    public function __construct(
        private readonly int $headingRow = 1,
    ) {
    }

    public function startRow(): int
    {
        return $this->headingRow;
    }

    public function limit(): int
    {
        return 1;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function map(mixed $row): array
    {
        return HeadingRowFormatter::format($row);
    }
}
