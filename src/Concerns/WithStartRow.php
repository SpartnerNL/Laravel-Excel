<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithStartRow
{
    public function startRow(): int;
}
