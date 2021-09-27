<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    /**
     * @param  Collection  $collection
     */
    public function collection(Collection $collection);
}
