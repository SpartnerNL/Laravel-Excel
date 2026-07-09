<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    /**
     * @param  Collection<array-key, mixed>  $collection
     */
    public function collection(Collection $collection): void;
}
