<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    /**
     * @return string|list<string>
     */
    public function uniqueBy(): string|array;
}
