<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    public function collection(Collection $collection);
}
