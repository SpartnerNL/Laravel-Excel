<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithHeadings
{
    public function headings(): array;
}
