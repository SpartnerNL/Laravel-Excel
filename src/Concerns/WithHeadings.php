<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithHeadings
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array;
}
