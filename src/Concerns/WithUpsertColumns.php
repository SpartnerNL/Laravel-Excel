<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpsertColumns
{
    /**
     * @return array<int, string>
     */
    public function upsertColumns(): array;
}
