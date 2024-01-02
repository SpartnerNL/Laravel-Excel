<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

interface FromQuery
{
    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query();
}
