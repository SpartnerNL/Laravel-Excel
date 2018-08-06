<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomCsvSettings
{
    /**
     * @return array
     */
    public function getCsvSettings(): array;
}
