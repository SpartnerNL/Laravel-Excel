<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithColumnWidths extends Export
{
    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array;
}
