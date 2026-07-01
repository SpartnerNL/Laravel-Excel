<?php

namespace Maatwebsite\Excel\Concerns;

interface WithColumnWidths
{
    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array;
}
