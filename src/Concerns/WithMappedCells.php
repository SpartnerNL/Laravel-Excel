<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithMappedCells
{
    /**
     * @return array<array-key, string|array<string, string>>
     */
    public function mapping(): array;
}
