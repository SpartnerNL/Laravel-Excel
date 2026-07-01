<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Laravel\Scout\Builder;

interface FromScout
{
    public function scout(): Builder;
}
