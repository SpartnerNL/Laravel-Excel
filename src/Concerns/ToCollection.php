<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    public function collection(Collection $collection): void;
}
