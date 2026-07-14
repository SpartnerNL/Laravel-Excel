<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithCustomCsvSettings extends Export, Import
{
    /**
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array;
}
