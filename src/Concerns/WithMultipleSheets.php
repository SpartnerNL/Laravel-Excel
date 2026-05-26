<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithMultipleSheets
{
    public function sheets(): array;
}
