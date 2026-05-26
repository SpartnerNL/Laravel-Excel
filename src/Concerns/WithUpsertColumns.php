<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpsertColumns
{
    public function upsertColumns(): array;
}
