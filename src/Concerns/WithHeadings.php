<?php

namespace Maatwebsite\Excel\Concerns;

interface WithHeadings
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array;
}
