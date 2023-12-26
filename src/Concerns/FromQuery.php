<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

interface FromQuery
{
    /**
     * @return Builder|EloquentBuilder|Relation|ScoutBuilder
     */
    public function query();
}
