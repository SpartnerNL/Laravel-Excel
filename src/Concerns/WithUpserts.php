<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    public function uniqueBy(): string|array;
}
