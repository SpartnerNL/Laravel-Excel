<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithMultipleSheets
{
    /**
     * @return array<int|string, object>
     */
    public function sheets(): array;
}
