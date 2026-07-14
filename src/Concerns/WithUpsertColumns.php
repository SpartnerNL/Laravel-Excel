<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpsertColumns
{
    /**
     * @return list<string>
     */
    public function upsertColumns(): array;
}
