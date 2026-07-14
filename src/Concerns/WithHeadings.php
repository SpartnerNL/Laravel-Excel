<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithHeadings extends Export
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array;
}
