<?php

namespace Maatwebsite\Excel\Concerns;

use Laravel\Scout\Builder;

interface FromScout
{
    public function scout(): Builder;
}
