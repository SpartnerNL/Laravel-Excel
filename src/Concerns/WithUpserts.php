<?php

namespace Maatwebsite\Excel\Concerns;

interface WithUpserts
{
    /**
     * @return string|array<int, string>
     */
    public function uniqueBy(): string|array;
}
