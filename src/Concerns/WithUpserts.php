<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    /**
     * @return string|array<int, string>
     */
    public function uniqueBy(): string|array;
}
