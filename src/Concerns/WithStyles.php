<?php

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface WithStyles
{
    /**
     * @return array<int|string, array<string, mixed>>|null
     */
    public function styles(Worksheet $sheet);
}
