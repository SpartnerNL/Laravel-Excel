<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomCsvSettings
{
    /**
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array;
}
