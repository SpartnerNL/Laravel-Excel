<?php

namespace Maatwebsite\Excel\Concerns;

interface WithUpsertColumns
{
    /**
     * @return array<int, string>
     */
    public function upsertColumns(): array;
}
