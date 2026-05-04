<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomCsvSettings
{
    public function getCsvSettings(): array;
}
