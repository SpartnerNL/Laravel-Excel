<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class FirstColumnReadFilter implements IReadFilter
{
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $columnAddress === 'A';
    }
}
