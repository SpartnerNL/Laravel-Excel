<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithStartRow extends Import
{
    public function startRow(): int;
}
