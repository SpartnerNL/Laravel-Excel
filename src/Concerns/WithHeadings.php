<?php

namespace Maatwebsite\Excel\Concerns;

interface WithHeadings
{
    /**
     * @return array
     */
    public function headings(): array;
}
