<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface FromCollection
{
    /**
     * @return LazyCollection|Collection
     */
    public function collection();
}
